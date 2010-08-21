<?PHP


# Basic application configuration
#
$app_config = array (
	# Dispatcher configuration
	"DISPATCH"		=> array (
		"categories"	=> array (				# Controller
			"MODEL"			=> "category",		# Model
			"TABLE"			=> "categories",	# Database table
			"PER_PAGE"		=> 5,				# Items per page
			"ACTIONS"		=> array ("index", "view", "new", "edit", "delete"),
		),

		"products"		=> array (				# Controller
			"MODEL"			=> "product",		# Model
			"TABLE"			=> "products",		# Database table
			"PER_PAGE"		=> 5,				# Items per page
			"ACTIONS"		=> array ("index", "view", "new", "edit", "delete"),
		),
	),

	# Defaults
	"DEFAULTS"		=> array (
		"CONTROLLER"	=> "categories",
		"ACTION"		=> "index",
	),

	# Development version, TRUE or FALSE
	"DEVELOPMENT"	=> TRUE,
	
	# Base URL to be prepended to all HTTP requests, path after
	# hostname should match rewrite_base in .htaccess files
	"BASE_URL"		=> "http://www.magrathea.com/~ctg/mvc",
);


# Database connection information
#
$db_config = array (
	# Database host
	"HOST"		=> "localhost",

	# Database port
	"PORT"		=> "3600",

	# Database name
	"NAME"		=> "mvctest",

	# Database user
	"USER"		=> "mvctest",

	# Database password
	"PASS"		=> "testestestes123",
);


# Framework configuration - these should rarely change
#
$framework_config = array (
	# Framework library files directories
	"FW_LIB_DIRS"	=> array (	"lib",
								"app/controllers",
								"app/models",
								"app/lib"),

	# Framework views directory
	"FW_VIEWS_DIR"	=> "app/views",

	# Framework name
	"FW_NAME"		=> "PHP MVC",

	# Framework copyright
	"FW_COPYRIGHT"	=> "&copy; 2010 MCS 'Net Productions",

	# Framework version
	"FW_VERSION"	=> "0.0.0",
);
