<?php
/**
 * GridFieldLitePaginator works like {@link GridFieldPaginator}, but doesn't manipulate the list.
 * 
 * @package forms
 * @subpackage fields-gridfield
 */
class GridFieldLitePaginator implements GridField_HTMLProvider, GridField_ActionProvider {
	
	/**
	 * Specifies default items per page
	 * 
	 * @config
	 * @var int
	 */
	private static $default_items_per_page = 15;
	
	/**
	 * @var int
	 */
	protected $itemsPerPage;


	/**
	 * Which template to use for rendering
	 * 
	 * @var string
	 */
	protected $itemClass = 'GridFieldPaginator_Row';

	/**
	 * See {@link setThrowExceptionOnBadDataType()}
	 */
	protected $throwExceptionOnBadDataType = true;

	/**
	 *
	 * @param int $itemsPerPage - How many items should be displayed per page
	 */
	public function __construct($itemsPerPage=null) {
		$this->itemsPerPage = $itemsPerPage
			?: Config::inst()->get('GridFieldLitePaginator', 'default_items_per_page');
	}
	
	/**
	 *
	 * @param GridField $gridField
	 * @return array
	 */
	public function getActions($gridField) {
		
		return array('paginate');
	}

	/**
	 *
	 * @param GridField $gridField
	 * @param string $actionName
	 * @param string $arguments
	 * @param array $data
	 * @return void
	 */
	public function handleAction(GridField $gridField, $actionName, $arguments, $data) {
		
		if($actionName !== 'paginate') {
			return;
		}
		$state = $this->getGridPagerState($gridField);
		$state->currentPage = (int)$arguments;
	}
	
	protected $totalItems = 0;

	function setTotalItems($total){
		$this->totalItems = $total;
	}
	
	/**
	 * Retrieves/Sets up the state object used to store and retrieve information
	 * about the current paging details of this GridField
	 * @param GridField $gridField
	 * @return GridState_Data 
	 */
	protected function getGridPagerState(GridField $gridField) {
		$state = $gridField->State->GridFieldPaginator;
		
		// Force the state to the initial page if none is set
		if(empty($state->currentPage)) $state->currentPage = 1;
		
		return $state;
	}

	/**
	 * Determines arguments to be passed to the template for building this field
	 * @return ArrayData|null If paging is available this will be an ArrayData 
	 * object of paging details with these parameters:
	 * <ul>
	 *	<li>OnlyOnePage:				boolean - Is there only one page?</li>
	 *  <li>FirstShownRecord:			integer - Number of the first record displayed</li>
	 *  <li>LastShownRecord:			integer - Number of the last record displayed</li>
	 *  <li>NumRecords:					integer - Total number of records</li>
	 *	<li>NumPages:					integer - The number of pages</li>
	 *	<li>CurrentPageNum (optional):	integer - If OnlyOnePage is false, the number of the current page</li>
	 *  <li>FirstPage (optional):		GridField_FormAction - Button to go to the first page</li>
	 *	<li>PreviousPage (optional):	GridField_FormAction - Button to go to the previous page</li>
	 *	<li>NextPage (optional):		GridField_FormAction - Button to go to the next page</li>
	 *	<li>LastPage (optional):		GridField_FormAction - Button to go to last page</li>
	 * </ul>
	 */
	public function getTemplateParameters(GridField $gridField) {
		
		$state = $this->getGridPagerState($gridField);

		// Figure out which page and record range we're on
		$totalRows = $this->totalItems;
		if(!$totalRows) return null;

		$totalPages = (int)ceil($totalRows/$this->itemsPerPage);
		if($totalPages == 0)
			$totalPages = 1;
		$firstShownRecord = ($state->currentPage - 1) * $this->itemsPerPage + 1;
		if($firstShownRecord > $totalRows)
			$firstShownRecord = $totalRows;
		$lastShownRecord = $state->currentPage * $this->itemsPerPage;
		if($lastShownRecord > $totalRows)
			$lastShownRecord = $totalRows;
		
		// If there is only 1 page for all the records in list, we don't need to go further 
		// to sort out those first page, last page, pre and next pages, etc
		// we are not render those in to the paginator.
		if($totalPages === 1){
			return new ArrayData(array(
				'OnlyOnePage' => true,
				'FirstShownRecord' => $firstShownRecord,
				'LastShownRecord' => $lastShownRecord,
				'NumRecords' => $totalRows,
				'NumPages' => $totalPages
			));
		} else {
			// First page button
			$firstPage = new GridField_FormAction($gridField, 'pagination_first', 'First', 'paginate', 1);
			$firstPage->addExtraClass('ss-gridfield-firstpage');
			if($state->currentPage == 1)
				$firstPage = $firstPage->performDisabledTransformation();

			// Previous page button
			$previousPageNum = $state->currentPage <= 1 ? 1 : $state->currentPage - 1;
			$previousPage = new GridField_FormAction($gridField, 'pagination_prev', 'Previous',
				'paginate', $previousPageNum);
			$previousPage->addExtraClass('ss-gridfield-previouspage');
			if($state->currentPage == 1)
				$previousPage = $previousPage->performDisabledTransformation();

			// Next page button
			$nextPageNum = $state->currentPage >= $totalPages ? $totalPages : $state->currentPage + 1;
			$nextPage = new GridField_FormAction($gridField, 'pagination_next', 'Next',
				'paginate', $nextPageNum);
			$nextPage->addExtraClass('ss-gridfield-nextpage');
			if($state->currentPage == $totalPages)
				$nextPage = $nextPage->performDisabledTransformation();

			// Last page button
			$lastPage = new GridField_FormAction($gridField, 'pagination_last', 'Last', 'paginate', $totalPages);
			$lastPage->addExtraClass('ss-gridfield-lastpage');
			if($state->currentPage == $totalPages)
				$lastPage = $lastPage->performDisabledTransformation();

			// Render in template
			return new ArrayData(array(
				'OnlyOnePage' => false,
				'FirstPage' => $firstPage,
				'PreviousPage' => $previousPage,
				'CurrentPageNum' => $state->currentPage,
				'NumPages' => $totalPages,
				'NextPage' => $nextPage,
				'LastPage' => $lastPage,
				'FirstShownRecord' => $firstShownRecord,
				'LastShownRecord' => $lastShownRecord,
				'NumRecords' => $totalRows
			));
		}
	}

	/**
	 *
	 * @param GridField $gridField
	 * @return array
	 */
	public function getHTMLFragments($gridField) {
		
		$forTemplate = $this->getTemplateParameters($gridField);
		if($forTemplate) {
			return array(
				'footer' => $forTemplate->renderWith($this->itemClass,
					array('Colspan'=>count($gridField->getColumns())))
			);
		}
	}

	/**
	 * @param Int
	 */
	public function setItemsPerPage($num) {
		$this->itemsPerPage = $num;
		return $this;
	}

	/**
	 * @return Int
	 */
	public function getItemsPerPage() {
		return $this->itemsPerPage;
	}

}
