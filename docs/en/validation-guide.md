# Validation System in LightWeight

## Introduction

The LightWeight validation system provides a robust and expressive interface for validating input data in your application. It allows you to easily check if the data submitted by users meets a specific set of rules before processing it, helping to maintain the integrity and security of your application.

## Basic Concepts

### Validation in Controllers

The most common method for validating data is through the Request object in your controllers:

```php
public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8|confirmed',
        'age' => 'nullable|integer|min:18',
    ]);
    
    // The data has been validated, proceed to use it
    User::create($validated);
    
    return redirect('/users')->with('success', 'User created successfully');
}
```

If validation fails, a redirect to the previous page with errors and the original input is automatically generated.

### Manual Validation

You can also manually create a validator:

```php
use LightWeight\Validation\Validator;

public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8|confirmed',
        'age' => 'nullable|integer|min:18',
    ]);
    
    if ($validator->fails()) {
        $errors = $validator->errors();
        // Process errors manually
        return back()->withErrors($errors)->withInput();
    }
    
    // Continue with processing
    User::create($validator->validated());
    
    return redirect('/users')->with('success', 'User created successfully');
}
```

## Available Validation Rules

LightWeight provides a wide variety of validation rules:

### Presence Rules

```
required         - The field must be present and not empty
required_if:foo,bar - Required if foo equals 'bar'
required_unless:foo,bar - Required unless foo equals 'bar'
required_with:foo,bar - Required if at least one of the fields (foo, bar) is present
required_with_all:foo,bar - Required if all fields (foo, bar) are present
required_without:foo,bar - Required if at least one of the fields (foo, bar) is not present
required_without_all:foo,bar - Required if all fields (foo, bar) are not present
prohibited - The field must be absent
prohibited_if:foo,bar - Prohibited if foo equals 'bar'
nullable - The field can be null
```

### Type Rules

```
string - The field must be a string
integer - The field must be an integer
numeric - The field must be a number (integer or decimal)
boolean - The field must be a boolean (true, false, 1, 0, "1", "0")
array - The field must be an array
object - The field must be an object
date - The field must be a valid date
file - The field must be an uploaded file
image - The field must be an image (jpg, jpeg, png, bmp, gif, svg, webp)
```

### Size Rules

```
min:value - Minimum value/length
max:value - Maximum value/length
between:min,max - Value/length between min and max
size:value - Exact size/length
```

### Comparison Rules

```
same:field - The field must match another field
different:field - The field must be different from another field
gt:field - The field must be greater than another field
gte:field - The field must be greater than or equal to another field
lt:field - The field must be less than another field
lte:field - The field must be less than or equal to another field
```

### Format Rules

```
email - The field must be a valid email
url - The field must be a valid URL
ip - The field must be a valid IP address
alpha - Letters only
alpha_dash - Letters, numbers, dashes, and underscores
alpha_num - Letters and numbers only
regex:pattern - Must match the regular expression pattern
```

### Database Rules

```
unique:table,column,except,idColumn - The field must be unique in the table
exists:table,column - The field must exist in the table
```

### Custom Rules

```php
// Define a rule as a Closure
$validator = Validator::make($request->all(), [
    'password' => [
        'required',
        'min:8',
        function ($attribute, $value, $fail) {
            if (!preg_match('/[A-Z]/', $value)) {
                $fail('The :attribute field must contain at least one uppercase letter.');
            }
        },
    ],
]);

// Define a rule as a class
class StrongPassword implements Rule
{
    public function validate($attribute, $value, $fail)
    {
        if (!preg_match('/[A-Z]/', $value)) {
            $fail('The :attribute field must contain at least one uppercase letter.');
        }
        
        if (!preg_match('/[0-9]/', $value)) {
            $fail('The :attribute field must contain at least one number.');
        }
        
        if (!preg_match('/[^A-Za-z0-9]/', $value)) {
            $fail('The :attribute field must contain at least one special character.');
        }
    }
}

// Using the custom rule
$validator = Validator::make($request->all(), [
    'password' => ['required', 'min:8', new StrongPassword],
]);
```

## Custom Error Messages

### Specific Messages

```php
$validated = $request->validate(
    [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'age' => 'required|integer|min:18',
    ],
    [
        'name.required' => 'The name is required.',
        'email.unique' => 'This email is already registered.',
        'age.min' => 'You must be at least 18 years old to register.',
    ]
);
```

### Messages by Attribute

```php
$validator = Validator::make($request->all(), $rules, $messages);

$validator->setAttributeNames([
    'email' => 'email address',
    'password' => 'password',
]);

// Now messages will use "email address" instead of "email"
```

### Global Messages

```php
// In a service provider
Validator::setDefaultMessages([
    'required' => 'The :attribute field is required.',
    'email' => 'The :attribute field must be a valid email address.',
    'unique' => 'The :attribute value is already in use.',
]);
```

## Array Validation

### Validating Simple Arrays

```php
$validator = Validator::make($request->all(), [
    'tags' => 'required|array|min:1|max:5',
    'tags.*' => 'string|max:50',
]);
```

### Validating Complex Arrays

```php
$validator = Validator::make($request->all(), [
    'users' => 'required|array|min:1',
    'users.*.name' => 'required|string|max:255',
    'users.*.email' => 'required|email|unique:users,email',
    'users.*.address.street' => 'required|string|max:255',
    'users.*.address.city' => 'required|string|max:100',
    'users.*.address.zip' => 'required|string|max:10',
]);
```

### Validating Number of Elements

```php
$validator = Validator::make($request->all(), [
    'items' => 'required|array|size:3',  // Exactly 3 elements
    'options' => 'required|array|min:2|max:5',  // Between 2 and 5 elements
]);
```

## Conditional Validation

### Validation Based on Other Inputs

```php
$validator = Validator::make($request->all(), [
    'is_company' => 'required|boolean',
    'company_name' => 'required_if:is_company,true|string|max:255',
    'tax_id' => 'required_if:is_company,true|string|max:20',
    
    'payment_type' => 'required|in:credit,transfer,cash',
    'card_number' => 'required_if:payment_type,credit|string|size:16',
    'account_number' => 'required_if:payment_type,transfer|string|max:30',
]);
```

### Validation with Callbacks

```php
$validator = Validator::make($request->all(), [
    'role' => 'required|in:user,admin,editor',
    'permissions' => function ($attribute, $value, $fail) use ($request) {
        if ($request->input('role') !== 'admin' && !empty($value)) {
            $fail('Permissions can only be assigned to administrators.');
        }
    },
]);
```

## File Validation

### Basic File Validation

```php
$validator = Validator::make($request->all(), [
    'photo' => 'required|file|max:2048',  // Maximum 2MB
    'document' => 'nullable|file|mimes:pdf,doc,docx|max:10240',  // Maximum 10MB, only PDF or Word
]);
```

### Image Validation

```php
$validator = Validator::make($request->all(), [
    'avatar' => 'required|image|max:2048|dimensions:min_width=100,min_height=100',
    'banner' => 'nullable|image|mimes:jpeg,png|dimensions:width=1200,height=400',
]);
```

### Multiple File Validation

```php
$validator = Validator::make($request->all(), [
    'documents' => 'required|array|min:1|max:5',
    'documents.*' => 'file|mimes:pdf|max:5120',
    
    'photos' => 'required|array|min:3|max:10',
    'photos.*' => 'image|mimes:jpeg,png|max:2048',
]);
```

## Date Validation

### Date Formats

```php
$validator = Validator::make($request->all(), [
    'birth_date' => 'required|date|before:today',
    'appointment' => 'required|date|after:tomorrow',
    'start_date' => 'required|date',
    'end_date' => 'required|date|after_or_equal:start_date',
]);
```

### Relative Date Validation

```php
$validator = Validator::make($request->all(), [
    'birth_date' => 'required|date|before:-18 years',  // At least 18 years old
    'publish_date' => 'nullable|date|after:today',  // Future date
    'expiration_date' => 'required|date|after:+30 days',  // At least 30 days in the future
]);
```

## Client-Side Validation

LightWeight provides a way to export validation rules to the frontend:

```php
// In your controller
public function create()
{
    $validationRules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8|confirmed',
    ];
    
    return view('users.create', [
        'validationRules' => ValidationRules::toJS($validationRules),
    ]);
}
```

In your view:

```html
<form id="registerForm">
    <div class="form-group">
        <label for="name">Name</label>
        <input type="text" id="name" name="name" class="form-control">
        <div class="invalid-feedback" id="name-error"></div>
    </div>
    <!-- Other fields... -->
    <button type="submit">Register</button>
</form>

<script>
    const validationRules = @json($validationRules);
    
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Use exported rules with your favorite JS validation library
        const validator = new Validator(formData, validationRules);
        
        if (validator.fails()) {
            const errors = validator.errors();
            // Display errors in the form
        } else {
            // Submit form
            this.submit();
        }
    });
</script>
```

## Extensibility

### Creating Custom Rules

```php
// Creating a Rule as a Class
namespace App\Rules;

use LightWeight\Validation\Contracts\Rule;

class PhoneNumber implements Rule
{
    protected $countryCode;
    
    public function __construct($countryCode = 'US')
    {
        $this->countryCode = $countryCode;
    }
    
    public function validate($attribute, $value, $fail)
    {
        if ($this->countryCode === 'ES') {
            // Validation for Spanish numbers
            if (!preg_match('/^(\+34|0034|34)?[6-9]\d{8}$/', $value)) {
                $fail('The :attribute field must be a valid Spanish phone number.');
            }
        } elseif ($this->countryCode === 'US') {
            // Validation for US numbers
            if (!preg_match('/^(\+1|001|1)?[2-9]\d{2}[2-9]\d{6}$/', $value)) {
                $fail('The :attribute field must be a valid US phone number.');
            }
        } else {
            // Generic validation
            if (!preg_match('/^\+?[0-9]{10,15}$/', $value)) {
                $fail('The :attribute field must be a valid phone number.');
            }
        }
    }
}
```

Using the custom rule:

```php
$validator = Validator::make($request->all(), [
    'phone' => ['required', new PhoneNumber('ES')],
    'us_contact' => ['nullable', new PhoneNumber('US')],
]);
```

### Global Rule Registration

```php
// In a service provider
public function boot()
{
    Validator::extend('phone', function ($attribute, $value, $parameters, $validator) {
        $countryCode = $parameters[0] ?? 'US';
        
        if ($countryCode === 'ES') {
            return preg_match('/^(\+34|0034|34)?[6-9]\d{8}$/', $value);
        } elseif ($countryCode === 'US') {
            return preg_match('/^(\+1|001|1)?[2-9]\d{2}[2-9]\d{6}$/', $value);
        }
        
        return preg_match('/^\+?[0-9]{10,15}$/', $value);
    });
    
    Validator::extendImplicit('password_strength', function ($attribute, $value, $parameters, $validator) {
        $minScore = $parameters[0] ?? 3;
        
        // Simplified password scoring implementation
        $score = 0;
        if (preg_match('/[A-Z]/', $value)) $score++;
        if (preg_match('/[a-z]/', $value)) $score++;
        if (preg_match('/[0-9]/', $value)) $score++;
        if (preg_match('/[^A-Za-z0-9]/', $value)) $score++;
        if (strlen($value) >= 10) $score++;
        
        return $score >= $minScore;
    });
}
```

Using globally registered rules:

```php
$validator = Validator::make($request->all(), [
    'phone' => 'required|phone:US',
    'password' => 'required|min:8|password_strength:4',
]);
```

## Advanced Validation

### Step-by-Step Validation

```php
// Step 1: Personal data
public function storeStep1(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'phone' => 'required|string|max:20',
    ]);
    
    // Save in session for later use
    session(['registration.step1' => $validated]);
    
    return redirect('/register/step2');
}

// Step 2: Address
public function storeStep2(Request $request)
{
    $validated = $request->validate([
        'address' => 'required|string|max:255',
        'city' => 'required|string|max:100',
        'zip' => 'required|string|max:10',
    ]);
    
    // Save in session for later use
    session(['registration.step2' => $validated]);
    
    return redirect('/register/step3');
}

// Step 3: Finalization
public function storeStep3(Request $request)
{
    $validated = $request->validate([
        'password' => 'required|min:8|confirmed',
        'terms' => 'required|accepted',
    ]);
    
    // Combine all data from the steps
    $userData = array_merge(
        session('registration.step1', []),
        session('registration.step2', []),
        $validated
    );
    
    // Create user
    $user = User::create($userData);
    
    // Clear session data
    session()->forget(['registration.step1', 'registration.step2']);
    
    // Log in and redirect
    auth()->login($user);
    
    return redirect('/dashboard');
}
```

### Model Validation

```php
// In a model
public function validate()
{
    $validator = Validator::make($this->attributes, [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,'.$this->id,
        'status' => 'required|in:active,inactive,pending',
    ]);
    
    if ($validator->fails()) {
        throw new ValidationException($validator);
    }
    
    return $this;
}

// In a controller
public function update(Request $request, $id)
{
    $user = User::find($id);
    $user->fill($request->all());
    
    try {
        $user->validate()->save();
        return redirect('/users')->with('success', 'User updated successfully');
    } catch (ValidationException $e) {
        return back()->withErrors($e->validator)->withInput();
    }
}
```

### Dynamic Form Validation

```php
public function store(Request $request)
{
    // Basic validation
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
    ]);
    
    // Validate dynamic fields based on user type
    if ($request->input('type') === 'company') {
        $validator->addRules([
            'company_name' => 'required|string|max:255',
            'tax_id' => 'required|string|max:20',
        ]);
    } elseif ($request->input('type') === 'individual') {
        $validator->addRules([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'id_number' => 'required|string|max:20',
        ]);
    }
    
    // Validate different fields based on country
    $countryCode = $request->input('country_code');
    if ($countryCode === 'ES') {
        $validator->addRules([
            'id_number' => 'required|regex:/^[0-9]{8}[A-Z]$/',
            'postal_code' => 'required|regex:/^[0-9]{5}$/',
        ]);
    } elseif ($countryCode === 'US') {
        $validator->addRules([
            'state' => 'required|string|size:2',
            'zip_code' => 'required|regex:/^[0-9]{5}(-[0-9]{4})?$/',
        ]);
    }
    
    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }
    
    // Continue processing
    User::create($validator->validated());
    
    return redirect('/users')->with('success', 'User created successfully');
}
```

## Best Practices

### Separation of Validation Logic

```php
// In a separate file, for example, App\Validators\UserValidator.php
class UserValidator
{
    public static function rules($userId = null)
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $userId,
            'password' => $userId ? 'nullable|min:8' : 'required|min:8',
            'role' => 'required|in:user,admin,editor',
        ];
    }
    
    public static function messages()
    {
        return [
            'name.required' => 'The name is required.',
            'email.unique' => 'This email is already registered.',
            'role.in' => 'The selected role is not valid.',
        ];
    }
    
    public static function validateForCreate($data)
    {
        return Validator::make($data, static::rules(), static::messages());
    }
    
    public static function validateForUpdate($data, $userId)
    {
        return Validator::make($data, static::rules($userId), static::messages());
    }
}

// In the controller
public function store(Request $request)
{
    $validator = UserValidator::validateForCreate($request->all());
    
    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }
    
    User::create($validator->validated());
    
    return redirect('/users')->with('success', 'User created successfully');
}

public function update(Request $request, $id)
{
    $validator = UserValidator::validateForUpdate($request->all(), $id);
    
    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }
    
    $user = User::find($id);
    $user->update($validator->validated());
    
    return redirect('/users')->with('success', 'User updated successfully');
}
```

### Rule Reuse

```php
// In a trait
trait ProductRules
{
    protected function getBaseRules()
    {
        return [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ];
    }
    
    protected function getDigitalProductRules()
    {
        return array_merge($this->getBaseRules(), [
            'file' => 'required|file|mimes:pdf,zip,mp3,mp4|max:102400',
            'download_limit' => 'nullable|integer|min:0',
        ]);
    }
    
    protected function getPhysicalProductRules()
    {
        return array_merge($this->getBaseRules(), [
            'weight' => 'required|numeric|min:0',
            'dimensions' => 'required|string',
            'stock' => 'required|integer|min:0',
        ]);
    }
}

// In a controller
use App\Traits\ProductRules;

class ProductController extends Controller
{
    use ProductRules;
    
    public function store(Request $request)
    {
        $rules = $request->input('type') === 'digital' 
            ? $this->getDigitalProductRules() 
            : $this->getPhysicalProductRules();
            
        $validated = $request->validate($rules);
        
        Product::create($validated);
        
        return redirect('/products')->with('success', 'Product created successfully');
    }
}
```

### Using Form Request for Validation

```php
// In a custom Form Request
namespace App\Http\Requests;

use LightWeight\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:user,admin,editor',
        ];
    }
    
    public function messages()
    {
        return [
            'name.required' => 'The name is required.',
            'email.unique' => 'This email is already registered.',
            'role.in' => 'The selected role is not valid.',
        ];
    }
    
    public function attributes()
    {
        return [
            'name' => 'full name',
            'email' => 'email address',
            'password' => 'password',
        ];
    }
    
    protected function prepareForValidation()
    {
        $this->merge([
            'name' => ucwords(strtolower($this->name)),
            'email' => strtolower($this->email),
        ]);
    }
    
    protected function passedValidation()
    {
        $this->replace([
            'password' => bcrypt($this->password),
        ]);
    }
}

// In the controller
public function store(StoreUserRequest $request)
{
    // Validation has already been performed
    $user = User::create($request->validated());
    
    return redirect('/users')->with('success', 'User created successfully');
}
```

## Conclusion

The LightWeight validation system provides a robust and flexible way to validate input data in your application. With its wide variety of predefined rules and its ability to extend with custom rules, you can ensure that only valid data is processed by your application.

Separating validation logic into specific classes or Form Requests allows for greater reusability and maintainability of code. Additionally, the ability to export rules to the frontend facilitates the implementation of consistent validation on both the server and client.

Use these tools to maintain the integrity of your data and provide a better user experience by displaying clear and helpful error messages.

> 🌐 [Documentación en Español](../es/validation-guide.md)
