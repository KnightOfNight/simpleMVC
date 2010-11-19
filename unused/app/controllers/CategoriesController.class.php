<?PHP

class CategoriesController extends BaseController {
	function beforeAction () {}

	function index () {
		$start = microtime (TRUE);

#		$this->_model->set_limit (5);
#
#		$this->_model->orderBy ('name', 'ASC');
#		$this->_model->showHasOne ();
#		$this->_model->showHasMany ();
#		$this->_model->where (Database::$AND, array ("parent_id" => 0));
#
#		$categories = $this->_model->search ();
#
#		$this->_view->set_variable ('categories', $categories);

		$this->_view = new BaseView ($this->_controller, "view");

		$end = microtime (TRUE);
		printf ("%.6fs<br />", $end - $start);
	}

#	function view ($categoryId = NULL) {
#		$model = new CategoriesModel ($this->_db);
#		$this->_model->set_limit (5);
#		$this->_model->where (Database::$AND, array ("parent_id" => $categoryId));
#		$this->_model->showHasOne ();
#		$this->_model->showHasMany ();
#		$subcategories = $this->_model->search ();
#		$this->_model->id = $categoryId;
#		$this->_model->showHasOne ();
#		$this->_model->showHasMany ();
#		$this->_view = new BaseView ($this->_controller, "view");
#		$this->_view->set_variable ('subcategories', $subcategories);
#		$this->_view->set_variable ('category', $category);
#	}

	function save_some_data () {
		$category = new CategoryModel($this->_db);

		$category->value("name", "cheyenne's category");
		$category->value("parent_id", "900");

		$new_id = $this->_db->save($category);

#		$this->_view->set_render_on (FALSE);
#
#		$this->_model->set_limit (0);
#		$this->_model->like ("title", "something");
#		$this->_model->search ();
#
#		$route = Route::make_route ("products", "get_list", NULL);
#
#		$products = Dispatch::go ($GLOBALS["dbh"], $route, FALSE);
#
#
#
#		$criteria = new Criteria;
#
#		$criteria->join ($other_model = new OtherModel ($GLOBALS["dbh"]));
#
#		$criteria->where ($this->_model->where (Criteria::$AND, array ("some column" => "some value")));
#		$criteria->orderby ($this->_model->orderby (Criteria::$SORT_ASC, array ("some column", "some other column")));
#
#		$criteria->join ($other_model = new OtherModel ($GLOBALS["dbh"]));
#
#		$this->_model->search ($criteria);
	}

	function view ($query) {

var_dump ($query);

		$page = $query[0];

var_dump ($page);

#		Dispatch::go($this->_db, "categories/save_some_data", FALSE);

$start = microtime (TRUE);

		$category = new CategoryModel($this->_db);
		$product = new ProductModel($this->_db);

		# CREATES NEW CRITERIA OBJECT, INITIALIZES THE LIST OF COLUMNS.
		$criteria = new Criteria($category);

		# ADDS A JOIN CLAUSE, ADDING A LEFT JOIN TO THE TABLE (ARG1) ON COL1 = COL2 (ARG2, ARG3).
		$criteria->leftJoin($product, "category.parent_id", "product.id");

		# SELECT THE COLUMNS TO RETURN.  ARG1 IS A HASH: KEY IS PROPER COLUMN
		# NAME IN FORMAT <TABLE>.<COLUMN>, VALUE IS COLUMN ALIAS, IF ANY.
		#
#		$criteria->selectCols(array ("product.id", "product.name", "category.name"));
#		$criteria->selectExpression("concat(category.name, ' - ', category.parent_id)", "smegcol");

		# SET WHERE.
#		$criteria->andWhere (array ($criteria->clause ("product.id", Criteria::$EQ, 10)));
#		$criteria->orWhere(array ($criteria->clause("product.id", Criteria::$EQ, 10), $criteria->clause("product.id", Criteria::$EQ, 100)), Criteria::$OR);

#		$criteria->andWhere (array ($criteria->clause ("product.name", Criteria::$LIKE, "%smeg%")));
#		$criteria->orWhere (array ($criteria->clause ("product.name", Criteria::$LIKE, "%poop%")));

		# ORDER BY
#		$criteria->orderby ("product.id,product.name,category.id", Criteria::$DESC);
		$criteria->orderby("category.name,category.id", Criteria::$ASC);

		# SET PAGE #.
		$criteria->page($page);

		# SET LIMIT ON ITEMS PER PAGE.
		$criteria->limit(25);

		$categories = $this->_db->select($criteria);

		$this->_view = new BaseView ($this->_controller, "view");
		$this->_view->setVariable("categories", $categories);
		$this->_view->pager(new Pager ($criteria, $this->_db));

$end = microtime (TRUE);
printf ("action time: %.6fs<br />", $end - $start);
	}

	function save () {
		$start = microtime (TRUE);

		$category = new CategoryModel($this->_db);

		$category->value("name", "cheyenne's category");
		$category->value("parent_id", "900");

		$new_id = $this->_db->save($category);

		$this->_view = new BaseView($this->_controller, "view");

		$end = microtime (TRUE);
		printf ("%.6fs<br />", $end - $start);
	}

	function update () {
		$start = microtime (TRUE);

		$category = new CategoryModel($this->_db);

		$category->value("id", "100");
		$category->value("name", "smeg's category");
		$category->value("parent_id", "100");

		$this->_db->save($category);
		
		$this->_view = new BaseView($this->_controller, "view");

		$end = microtime (TRUE);
		printf ("%.6fs<br />", $end - $start);
	}
	
	function delete () {
		$category = new CategoryModel ($this->_db);

		for ($i = 100; $i < 140; $i++) {
			$category->value ("id", $i);
			$this->_db->delete ($category);
		}
		
		$this->_view = new BaseView ($this->_controller, "view");
	}
	
	function afterAction () {}
}
