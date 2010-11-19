<?PHP

class ProductsController extends BaseController {
	function beforeAction () { }

	function view ($id = NULL) {
		$this->_product->id = $id;
		$this->_product->showHasOne ();
		$this->_product->showHMABTM ();

		$product = $this->_product->search ();

		$this->_view->set ('product', $product);
	}

	function afterAction () { }
}
