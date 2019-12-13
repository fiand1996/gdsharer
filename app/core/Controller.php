<?php
/**
 * Controller Core
 *
 * @date 07 June 2019
 * @version 1.0
 * @copyright Fiand T <fiand96@yahoo.com>
 * @author Fiand T (https://github.com/fiand1996)
 */
class Controller
{   
    /**
     * Assosiative array
     * Key: will be converted to variable for view
     * Value: value of the variable with name Key
     * @var array
     */
    protected $variables;

    /**
     * JSON output response holder
     * @var \stdClass
     */
    protected $response;

    /**
     * Initialize variables
     * @param array $variables  [description]
     */
    public function __construct($variables = array())
    {
        $this->variables = array();
        $this->response = new stdClass;
    }

    /**
     * Get model
     * @param  string|array $name name of model
     * @param  array|string $args Array of arguments for model constructor
     * @return null|mixed       
     */
    public static function model($name, $args=array())
    {
        if (is_array($name)) {
            if (count($name) != 2) {
                throw new Exception('Invalid parameter');
            }

            $file = $name[0];
            $class = $name[1];
        } else {
            $file = APPPATH."/models/".$name."Model.php";

            if (! file_exists($file)) {
                throw new Exception("Model {$name} not found");
            }
            
            $class = $name."Model";
        }

        if (file_exists($file)) {
            require_once $file;

            if (!is_array($args)) {
                $args = array($args);
            }

            $reflector = new ReflectionClass($class);
            return $reflector->newInstanceArgs($args);
        }

        return null;
    }

    /**
     * View
     * @param  string $view name of view file
     * @param  string $context 
     * @return void       
     */
    public function view($view, $context = "app")
    {
        $path = APPPATH . "/views/" .  $context;
        $loader = new \Twig\Loader\FilesystemLoader($path);
        
        $twig = new \Twig\Environment($loader, [
            'debug' => true
        ]);

        include APPPATH . "/inc/twig.inc.php";

        $template = $twig->load($view . ".html");
        $html = $template->render($this->variables);
        
        $parser = \WyriHaximus\HtmlCompress\Factory::construct();
        
        echo $parser->compress($html);
    }

    /**
     * Set new variable for view.
     * @param string $name  Name of the variable.
     * @param mixed $value 
     */
    public function setVariable($name, $value)
    {
        $this->variables[$name] = $value;
        return $this;
    }


    /**
     * Get variable
     * @param  string $name Name of the varaible.
     * @return mixed       
     */
    public function getVariable($name)
    {
        return isset($this->variables[$name]) ? $this->variables[$name] : null;
    }


    /**
     * Print json(or jsonp) string and exit;
     * @return void 
     */
    protected function jsonecho($response = null)
    {
        if (is_null($response)) {
            $response = $this->response;
        }

        $CSRFToken = $this->getVariable("CSRFToken");
        $response->token = $CSRFToken;

        return jsonecho($response);
    }
}
