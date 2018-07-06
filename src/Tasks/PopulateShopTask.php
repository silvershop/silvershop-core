<?php

namespace SilverShop\Tasks;

use Page;
use SilverShop\Page\AccountPage;
use SilverShop\Page\CartPage;
use SilverShop\Page\CheckoutPage;
use SilverShop\Page\Product;
use SilverShop\Page\ProductCategory;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\BuildTask;
use SilverStripe\Dev\FixtureFactory;
use SilverStripe\Dev\YamlFixture;
use SilverStripe\ORM\DB;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * Populate shop task
 */
class PopulateShopTask extends BuildTask
{
    protected $title = 'Populate Shop';

    protected $description = 'Creates dummy account page, products, checkout page, terms page.';

    private static $segment = 'PopulateShopTask';

    public function run($request)
    {
        $this->extend('beforePopulate');

        $factory = Injector::inst()->create(FixtureFactory::class);

        $parentid = 0;

        $fixtureDir = realpath(__DIR__ . '/../../tests/php/Fixtures');

        //create products
        if (!Product::get()->count()) {
            $fixture = YamlFixture::create($fixtureDir . '/dummyproducts.yml');
            $fixture->writeInto($factory);//needs to be a data model

            $shoppage = ProductCategory::get()->filter('URLSegment', 'shop')->first();
            $parentid = $shoppage->ID;

            $categoriestopublish = array(
                'products',
                'electronics',
                'apparel',
                'entertainment',
                'music',
                'movies',
                'drama',
                'toys',
                'food',
                'books',
                'jewellery',
                'furniture',
                'kitchen',
                'bedroom',
                'stationery',
            );
            foreach ($categoriestopublish as $categoryname) {
                $factory->get(ProductCategory::class, $categoryname)->publishSingle();
            }
            $productstopublish = [
                'mp3player',
                'hdtv',
                'socks',
                'tshirt',
                'beachball',
                'hoop',
                'kite',
                'genericmovie',
                'lemonchicken',
                'ring',
                'book',
                'lamp',
                'paper',
                'pens',
            ];
            foreach ($productstopublish as $productname) {
                $factory->get(Product::class, $productname)->publishSingle();
            }
            DB::alteration_message('Created dummy products and categories', 'created');
        } else {
            echo '<p style="color:orange;">Products and categories were not created because some already exist.</p>';
        }

        //cart page
        if (!$page = CartPage::get()->first()) {
            $fixture = YamlFixture::create($fixtureDir . '/pages/Cart.yml');
            $fixture->writeInto($factory);
            $page = $factory->get(CartPage::class, 'cart');
            $page->ParentID = $parentid;
            $page->writeToStage('Stage');
            $page->publishSingle();
            DB::alteration_message('Cart page created', 'created');
        }

        //checkout page
        if (!$page = CheckoutPage::get()->first()) {
            $fixture = YamlFixture::create($fixtureDir . '/pages/Checkout.yml');
            $fixture->writeInto($factory);
            $page = $factory->get(CheckoutPage::class, 'checkout');
            $page->ParentID = $parentid;
            $page->writeToStage('Stage');
            $page->publishSingle();
            DB::alteration_message('Checkout page created', 'created');
        }

        //account page
        if (!AccountPage::get()->first()) {
            $fixture = YamlFixture::create($fixtureDir . '/pages/Account.yml');
            $fixture->writeInto($factory);
            $page = $factory->get(AccountPage::class, 'account');
            $page->ParentID = $parentid;
            $page->writeToStage('Stage');
            $page->publishSingle();
            DB::alteration_message('Account page \'Account\' created', 'created');
        }

        //terms page
        if (!$termsPage = Page::get()->filter('URLSegment', 'terms-and-conditions')->first()) {
            $fixture = YamlFixture::create($fixtureDir . '/pages/TermsConditions.yml');
            $fixture->writeInto($factory);
            $page = $factory->get('Page', 'termsconditions');
            $page->ParentID = $parentid;
            $page->writeToStage('Stage');
            $page->publishSingle();
            //set terms page id in config
            $config = SiteConfig::current_site_config();
            $config->TermsPageID = $page->ID;
            $config->write();
            DB::alteration_message('Terms and conditions page created', 'created');
        }

        //countries config - removes some countries
        $siteconfig = SiteConfig::current_site_config();
        if (empty($siteconfig->AllowedCountries)) {
            $siteconfig->AllowedCountries =
                'AF,AL,DZ,AS,AD,AO,AG,AR,AM,AU,AT,AZ,BS,BH,
                 BD,BB,BY,BE,BZ,BJ,BT,BO,BA,BW,BR,BN,BG,BF,BI,
                 KH,CM,CA,CV,CF,TD,CL,CN,CO,KM,CG,CR,CI,HR,CU,
                 CY,CZ,DK,DJ,DM,DO,EC,EG,SV,GQ,ER,EE,ET,FJ,FI,
                 FR,GA,GM,GE,DE,GH,GR,GD,GT,GN,GW,GY,HT,HN,HK,
                 HU,IS,IN,ID,IR,IQ,IE,IL,IT,JM,JP,JO,KZ,KE,KI,
                 KP,KR,KW,KG,LA,LV,LB,LS,LR,LY,LI,LT,LU,MG,MW,
                 MY,MV,ML,MT,MH,MR,MU,MX,FM,MD,MC,MN,MS,MA,MZ,
                 MM,NA,NR,NP,NL,NZ,NI,NE,NG,NO,OM,PK,PW,PA,PG,
                 PY,PE,PH,PL,PT,QA,RO,RU,RW,KN,LC,VC,WS,SM,ST,
                 SA,SN,SC,SL,SG,SK,SI,SB,SO,ZA,ES,LK,SD,SR,SZ,
                 SE,CH,SY,TJ,TZ,TH,TG,TO,TT,TN,TR,TM,TV,UG,UA,
                 AE,GB,US,UY,UZ,VU,VE,VN,YE,YU,ZM,ZW';
            $siteconfig->write();
        }
        $this->extend('afterPopulate');
    }
}
