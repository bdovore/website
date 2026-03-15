# PHP MVC Framework Skill

## Overview
This skill provides guidance for working with the custom PHP MVC framework used in the current codebase. The framework is located in `library/Bdo` and consists of three main components: Controller, View, and Database handling.

## Framework Structure

### Controller (`library/Bdo/Controller.php`)
- **Purpose**: Handles HTTP requests and business logic
- **Key Features**:
  - Automatically initializes a View instance
  - Loads appropriate view files based on controller and action
  - Can load models dynamically
- **Usage**:
  ```php
  class MyController extends Bdo_Controller {
      public function indexAction() {
          $this->loadModel('MyModel');
          $data = $this->MyModel->getData();
          $this->view->addVar('data', $data);
      }
  }
  ```

### View (`library/Bdo/View.php`)
- **Purpose**: Manages presentation layer and rendering
- **Key Features**:
  - Template system using `.phtml` files
  - Supports adding CSS/JS files
  - Manages page titles, headers, and variables
  - Handles both full page renders and XHR (AJAX) responses
- **Common Methods**:
  - `addPhtmlFile($filename, $mask)`: Add a template file
  - `addVar($name, $value)`: Pass data to templates
  - `addCssFile($filename)`: Add CSS file
  - `addJavascriptFile($filename)`: Add JS file
  - `flush()`: Render and output the view
  - `render()`: Alternative rendering method

### Database (`library/Bdo/Db.php`)
- **Purpose**: Database abstraction layer
- **Key Features**:
  - MySQL connection management
  - Query building and execution
  - Result handling
  - Prepared statements support

## File Structure Conventions

### Controllers
- Location: `mvc/controllers/`
- Naming: `[ControllerName]Controller.php`
- Example: `UserController.php`

### Models
- Location: `mvc/models/`
- Naming: `[ModelName].php`
- Example: `User.php`

### Views
- Location: `mvc/views/views_controllers/`
- Naming: `[viewname].phtml`
- Layouts: `mvc/views/layout/`
- Helpers: `mvc/views/helpers/`

### Configuration
- Main configuration handled through `Bdo_Cfg` class
- Database configuration typically in config files

## Request Flow

1. **Routing**: Requests are routed to appropriate controller based on URL
2. **Controller Initialization**: Controller class is instantiated
3. **Action Execution**: Specific action method is called
4. **Model Loading**: Controller loads required models
5. **Data Processing**: Business logic executed
6. **View Setup**: Controller configures view with data
7. **Rendering**: View renders templates and outputs response

## Key Classes

### Bdo_Cfg
- Configuration management
- Provides access to application settings
- User session management

### Bdo_User
- User authentication and authorization
- Access level checking
- User session management

## Development Patterns

### Controller Methods
- Action methods typically named `[action]Action()`
- Example: `indexAction()`, `viewAction()`, `editAction()`

### View Variables
- Data passed to views using `$this->view->addVar()`
- Accessed in templates as variables

### Template System
- Uses PHP mixed with HTML in `.phtml` files
- Variables from controller available in template scope
- Supports partials and layout inheritance

## Common Workflows

### Creating a New Page
1. Create controller class in `mvc/controllers/`
2. Add action method for the page
3. Create corresponding `.phtml` template in `mvc/views/views_controllers/`
4. Add any required CSS/JS files
5. Configure routing if needed

### Database Operations
1. Load model in controller: `$this->loadModel('ModelName')`
2. Call model methods to get/set data
3. Pass data to view: `$this->view->addVar('data', $data)`
4. Display data in template

## Best Practices

1. **Separation of Concerns**: Keep business logic in controllers, presentation in views
2. **Thin Controllers**: Move complex logic to models
3. **View Variables**: Pass only necessary data to views
4. **Template Organization**: Use partials for reusable components
5. **Error Handling**: Use framework's error handling mechanisms

## Debugging Tips

1. Check view variables with `$this->view->a_var`
2. Use `flush()` for immediate output debugging
3. Verify template file paths and naming
4. Check model loading and database connections

## Framework Limitations

1. **Custom Implementation**: Not based on standard PHP frameworks
2. **Dynamic Properties**: Uses `#[AllowDynamicProperties]` attribute
3. **Tight Coupling**: Some components tightly integrated
4. **Limited Documentation**: Framework-specific features may not be well-documented

## Migration Considerations

When working with this framework:
- Study existing controllers and views for patterns
- Pay attention to how models interact with database
- Understand the view rendering flow
- Note the use of configuration variables
- Observe how user authentication is handled
