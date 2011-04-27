<?php
/*
UPDATES

    2008-08-10  Fixed CSS comment stripping regex to add PCRE_DOTALL (changed from '/\/\*.*\*\//U' to '/\/\*.*\*\//sU')
    2008-08-18  Added lines instructing DOMDocument to attempt to normalize HTML before processing
    2008-10-20  Fixed bug with bad variable name... Thanks Thomas!
    2008-03-02  Added licensing terms under the MIT License; Only remove unprocessable HTML tags if they exist in the array
    2009-06-03  Normalize existing CSS (style) attributes in the HTML before we process the CSS.
                Made it so that the display:none stripper doesn't require a trailing semi-colon.
    2009-08-13  Added support for subset class values (e.g. "p.class1.class2"). Added better protection for bad css attributes.
                Fixed support for HTML entities.
    2009-08-17  Fixed CSS selector processing so that selectors are processed by precedence/specificity, and not just in order.
    2009-10-29  Fixed so that selectors appearing later in the CSS will have precedence over identical selectors appearing earlier.
    2009-11-04  Explicitly declared static functions static to get rid of E_STRICT notices.

*/
class Emogrifier {

    private $html = '';
    private $css = '';
    private $unprocessableHTMLTags = array('wbr');

    public function __construct($html = '', $css = '') {
        $this->html = $html;
        $this->css  = $css;
    }

    public function setHTML($html = '') { $this->html = $html; }
    public function setCSS($css = '') { $this->css = $css; }

	// there are some HTML tags that DOMDocument cannot process, and will throw an error if it encounters them.
	// these functions allow you to add/remove them if necessary.
	// it only strips them from the code (does not remove actual nodes).
    public function addUnprocessableHTMLTag($tag) { $this->unprocessableHTMLTags[] = $tag; }
    public function removeUnprocessableHTMLTag($tag) {
        if (($key = array_search($tag,$this->unprocessableHTMLTags)) !== false)
            unset($this->unprocessableHTMLTags[$key]);
    }

    // applies the CSS you submit to the html you submit. places the css inline
	public function emogrify() {
	    $body = $this->html;
	    // process the CSS here, turning the CSS style blocks into inline css
	    if (count($this->unprocessableHTMLTags)) {
            $unprocessableHTMLTags = implode('|',$this->unprocessableHTMLTags);
            $body = preg_replace("/<($unprocessableHTMLTags)[^>]*>/i",'',$body);
	    }

        $encoding = mb_detect_encoding($body);
        $body = mb_convert_encoding($body, 'HTML-ENTITIES', $encoding);

        $xmldoc = new DOMDocument;
		$xmldoc->encoding = $encoding;
		$xmldoc->strictErrorChecking = false;
		$xmldoc->formatOutput = true;
        $xmldoc->loadHTML($body);
		$xmldoc->normalizeDocument();

		$xpath = new DOMXPath($xmldoc);

        // before be begin processing the CSS file, parse the document and normalize all existing CSS attributes (changes 'DISPLAY: none' to 'display: none');
        // we wouldn't have to do this if DOMXPath supported XPath 2.0.
        $nodes = @$xpath->query('//*[@style]');
        if ($nodes->length > 0) foreach ($nodes as $node) $node->setAttribute('style',preg_replace('/[A-z\-]+(?=\:)/Se',"strtolower('\\0')",$node->getAttribute('style')));

		// get rid of css comment code
		$re_commentCSS = '/\/\*.*\*\//sU';
		$css = preg_replace($re_commentCSS,'',$this->css);

		// process the CSS file for selectors and definitions
		$re_CSS = '/^\s*([^{]+){([^}]+)}/mis';
		preg_match_all($re_CSS,$css,$matches);

        $all_selectors = array();
		foreach ($matches[1] as $key => $selectorString) {
		    // if there is a blank definition, skip
		    if (!strlen(trim($matches[2][$key]))) continue;

            // else split by commas and duplicate attributes so we can sort by selector precedence
		    $selectors = explode(',',$selectorString);
		    foreach ($selectors as $selector) {
                // don't process pseudo-classes
		        if (strpos($selector,':') !== false) continue;
                $all_selectors[] = array('selector' => $selector,
                                         'attributes' => $matches[2][$key],
                                         'index' => $key, // keep track of where it appears in the file, since order is important
                );
            }
        }

        // now sort the selectors by precedence
        usort($all_selectors, array('self','sortBySelectorPrecedence'));

        foreach ($all_selectors as $value) {

            // query the body for the xpath selector
            $nodes = $xpath->query($this->translateCSStoXpath(trim($value['selector'])));

            foreach($nodes as $node) {
                // if it has a style attribute, get it, process it, and append (overwrite) new stuff
                if ($node->hasAttribute('style')) {
                    // break it up into an associative array
                    $oldStyleArr = $this->cssStyleDefinitionToArray($node->getAttribute('style'));
                    $newStyleArr = $this->cssStyleDefinitionToArray($value['attributes']);

                    // new styles overwrite the old styles (not technically accurate, but close enough)
                    $combinedArr = array_merge($oldStyleArr,$newStyleArr);
                    $style = '';
                    foreach ($combinedArr as $k => $v) $style .= (strtolower($k) . ':' . $v . ';');
                } else {
                    // otherwise create a new style
                    $style = trim($value['attributes']);
                }
                $node->setAttribute('style',$style);
            }
        }

		// This removes styles from your email that contain display:none. You could comment these out if you want.
        $nodes = $xpath->query('//*[contains(translate(@style," ",""),"display:none")]');
        foreach ($nodes as $node) $node->parentNode->removeChild($node);

		return $xmldoc->saveHTML();

	}

    private static function sortBySelectorPrecedence($a, $b) {
        $precedenceA = self::getCSSSelectorPrecedence($a['selector']);
        $precedenceB = self::getCSSSelectorPrecedence($b['selector']);

        // we want these sorted ascendingly so selectors with lesser precedence get processed first and
        // selectors with greater precedence get sorted last
        return ($precedenceA == $precedenceB) ? ($a['index'] < $b['index'] ? -1 : 1) : ($precedenceA < $precedenceB ? -1 : 1);
    }

    private static function getCSSSelectorPrecedence($selector) {
        $precedence = 0;
        $value = 100;
        $search = array('\#','\.',''); // ids: worth 100, classes: worth 10, elements: worth 1

        foreach ($search as $s) {
            if (trim($selector == '')) break;
            $num = 0;
            $selector = preg_replace('/'.$s.'\w+/','',$selector,-1,$num);
            $precedence += ($value * $num);
            $value /= 10;
        }

        return $precedence;
    }

	// right now we only support CSS 1 selectors, but include CSS2/3 selectors are fully possible.
	// http://plasmasturm.org/log/444/
	private function translateCSStoXpath($css_selector) {
	    // returns an Xpath selector
	    $search = array(
	                       '/\s+>\s+/', // Matches any F element that is a child of an element E.
	                       '/(\w+)\s+\+\s+(\w+)/', // Matches any F element that is a child of an element E.
	                       '/\s+/', // Matches any F element that is a descendant of an E element.
	                       '/(\w+)?\#([\w\-]+)/e', // Matches id attributes
	                       '/(\w+|\*)?((\.[\w\-]+)+)/e', // Matches class attributes
	    );
	    $replace = array(
	                       '/',
	                       '\\1/following-sibling::*[1]/self::\\2',
	                       '//',
	                       "(strlen('\\1') ? '\\1' : '*').'[@id=\"\\2\"]'",
	                       "(strlen('\\1') ? '\\1' : '*').'[contains(concat(\" \",@class,\" \"),concat(\" \",\"'.implode('\",\" \"))][contains(concat(\" \",@class,\" \"),concat(\" \",\"',explode('.',substr('\\2',1))).'\",\" \"))]'",
	    );
	    return '//'.preg_replace($search,$replace,trim($css_selector));
	}

	private function cssStyleDefinitionToArray($style) {
	    $definitions = explode(';',$style);
	    $retArr = array();
	    foreach ($definitions as $def) {
            if (empty($def) || strpos($def, ':') === false) continue;
    	    list($key,$value) = explode(':',$def,2);
    	    if (empty($key) || empty($value)) continue;
    	    $retArr[trim($key)] = trim($value);
	    }
	    return $retArr;
	}
}
