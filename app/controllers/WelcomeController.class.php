<?PHP

class WelcomeController extends BaseController {
	function beforeAction () {
		Session::start();
	}


	function frontpage ($query) {
		$this->view = new View($this->controller, "frontpage", $query);

		if ( isset ($_POST["username"]) AND isset ($_POST["password"]) ) {
			$username = $_POST["username"];
			$password = $_POST["password"];

			# Replace with database lookup for username, password.
			if ($username === "ctg" AND $password === "smeg") {
				# LOGIN OK
				Login::authorize($username);

				if ( isset ($_POST["remember"]) AND ($_POST["remember"] == "on") ) {
					Login::remember();
				}

				Browser::redirect("welcome");
			} else {
				# LOGIN NOT OK
			}
		}
	}


	function logout ($query) {
		Session::stop();

		Browser::redirect("welcome");
	}


	function _loginform ($query) {
		$this->view = new View($this->controller, "loginform", $query);
		$this->view->renderHF(FALSE);

		$this->view->setVariable("passed_username", "");
		$this->view->setVariable("passed_remember", "");

		if (isset ($_POST["username"])) {
			$this->view->setVariable("passed_username", $_POST["username"]);
		}
		if (isset ($_POST["remember"])) {
			$this->view->setVariable("passed_remember", "checked");
		}
	}


	function parselogin ($query) {
	}


	function browse ($query) {
		$this->view = new View($this->controller, "browse", $query);
	}


	function results ($query) {
		$page = isset ($query[0]) ? (int) $query[0] : 1;

		$criteria = new Criteria(new ItemModel($this->db));

		$criteria->leftJoin(new TitleModel($this->db), "item.title_id", "title.id");

		$criteria->leftJoin(new PublisherModel($this->db), "title.publisher_id", "publisher.id");

		$criteria->selectCols(array ("title.title", "publisher.name"));
		$criteria->orderby(array("title.title"), Criteria::$ASC);
		$criteria->limit(15);
		$criteria->page($page);

		$pager = new Pager($criteria, $this->db);

		$items = $this->db->select($criteria);

# my_var_dump ("query", $this->db->lastQuery());

		$this->view = new View($this->controller, "results", $query);
		$this->view->renderHF(FALSE);
		$this->view->setVariable("items", $items);

#		$this->view->pager(new Pager($criteria, $this->db));
		$this->view->pager($pager);
	}


	function afterAction () {}
}
