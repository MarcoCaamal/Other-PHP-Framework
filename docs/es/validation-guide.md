# Sistema de Validación en LightWeight

## Introducción

El sistema de validación de LightWeight proporciona una interfaz robusta y expresiva para validar datos de entrada en tu aplicación. Permite comprobar fácilmente si los datos enviados por los usuarios cumplen con un conjunto específico de reglas antes de procesarlos, lo que ayuda a mantener la integridad y seguridad de tu aplicación.

## Conceptos Básicos

### Validación en Controladores

El método más común para validar datos es a través del objeto Request en tus controladores:

```php
public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8|confirmed',
        'age' => 'nullable|integer|min:18',
    ]);
    
    // Los datos han sido validados, procede a utilizarlos
    User::create($validated);
    
    return redirect('/users')->with('success', 'Usuario creado correctamente');
}
```

Si la validación falla, se genera automáticamente una redirección a la página anterior con los errores y la entrada original.

### Validación Manual

También puedes crear manualmente un validador:

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
        // Procesar errores manualmente
        return back()->withErrors($errors)->withInput();
    }
    
    // Continuar con el procesamiento
    User::create($validator->validated());
    
    return redirect('/users')->with('success', 'Usuario creado correctamente');
}
```

## Reglas de Validación Disponibles

LightWeight proporciona una amplia variedad de reglas de validación:

### Reglas de Presencia

```
required         - El campo debe estar presente y no vacío
required_if:foo,bar - Requerido si foo es igual a 'bar'
required_unless:foo,bar - Requerido a menos que foo sea igual a 'bar'
required_with:foo,bar - Requerido si al menos uno de los campos (foo, bar) está presente
required_with_all:foo,bar - Requerido si todos los campos (foo, bar) están presentes
required_without:foo,bar - Requerido si al menos uno de los campos (foo, bar) no está presente
required_without_all:foo,bar - Requerido si todos los campos (foo, bar) no están presentes
prohibited - El campo debe estar ausente
prohibited_if:foo,bar - Prohibido si foo es igual a 'bar'
nullable - El campo puede ser null
```

### Reglas de Tipo

```
string - El campo debe ser una cadena de texto
integer - El campo debe ser un número entero
numeric - El campo debe ser un número (entero o decimal)
boolean - El campo debe ser un booleano (true, false, 1, 0, "1", "0")
array - El campo debe ser un array
object - El campo debe ser un objeto
date - El campo debe ser una fecha válida
file - El campo debe ser un archivo subido
image - El campo debe ser una imagen (jpg, jpeg, png, bmp, gif, svg, webp)
```

### Reglas de Tamaño

```
min:value - Valor/longitud mínima
max:value - Valor/longitud máxima
between:min,max - Valor/longitud entre min y max
size:value - Tamaño/longitud exacto
```

### Reglas de Comparación

```
same:field - El campo debe coincidir con otro campo
different:field - El campo debe ser diferente a otro campo
gt:field - El campo debe ser mayor que otro campo
gte:field - El campo debe ser mayor o igual que otro campo
lt:field - El campo debe ser menor que otro campo
lte:field - El campo debe ser menor o igual que otro campo
```

### Reglas de Formato

```
email - El campo debe ser un email válido
url - El campo debe ser una URL válida
ip - El campo debe ser una dirección IP válida
alpha - Solo letras
alpha_dash - Letras, números, guiones y guiones bajos
alpha_num - Solo letras y números
regex:pattern - Debe coincidir con el patrón de expresión regular
```

### Reglas de Base de Datos

```
unique:table,column,except,idColumn - El campo debe ser único en la tabla
exists:table,column - El campo debe existir en la tabla
```

### Reglas Personalizadas

```php
// Definir una regla como Closure
$validator = Validator::make($request->all(), [
    'password' => [
        'required',
        'min:8',
        function ($attribute, $value, $fail) {
            if (!preg_match('/[A-Z]/', $value)) {
                $fail('El campo :attribute debe contener al menos una letra mayúscula.');
            }
        },
    ],
]);

// Definir una regla como clase
class StrongPassword implements Rule
{
    public function validate($attribute, $value, $fail)
    {
        if (!preg_match('/[A-Z]/', $value)) {
            $fail('El campo :attribute debe contener al menos una letra mayúscula.');
        }
        
        if (!preg_match('/[0-9]/', $value)) {
            $fail('El campo :attribute debe contener al menos un número.');
        }
        
        if (!preg_match('/[^A-Za-z0-9]/', $value)) {
            $fail('El campo :attribute debe contener al menos un carácter especial.');
        }
    }
}

// Uso de la regla personalizada
$validator = Validator::make($request->all(), [
    'password' => ['required', 'min:8', new StrongPassword],
]);
```

## Mensajes de Error Personalizados

### Mensajes Específicos

```php
$validated = $request->validate(
    [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'age' => 'required|integer|min:18',
    ],
    [
        'name.required' => 'El nombre es obligatorio.',
        'email.unique' => 'Este correo ya está registrado.',
        'age.min' => 'Debes tener al menos 18 años para registrarte.',
    ]
);
```

### Mensajes por Atributo

```php
$validator = Validator::make($request->all(), $rules, $messages);

$validator->setAttributeNames([
    'email' => 'dirección de correo',
    'password' => 'contraseña',
]);

// Ahora los mensajes usarán "dirección de correo" en lugar de "email"
```

### Mensajes Globales

```php
// En un proveedor de servicios
Validator::setDefaultMessages([
    'required' => 'El campo :attribute es obligatorio.',
    'email' => 'El campo :attribute debe ser una dirección de correo válida.',
    'unique' => 'El valor del campo :attribute ya está en uso.',
]);
```

## Validación de Arrays

### Validar Arrays Simples

```php
$validator = Validator::make($request->all(), [
    'tags' => 'required|array|min:1|max:5',
    'tags.*' => 'string|max:50',
]);
```

### Validar Arrays Complejos

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

### Validar Número de Elementos

```php
$validator = Validator::make($request->all(), [
    'items' => 'required|array|size:3',  // Exactamente 3 elementos
    'options' => 'required|array|min:2|max:5',  // Entre 2 y 5 elementos
]);
```

## Validación Condicional

### Validación basada en Otras Entradas

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

### Validación con Callbacks

```php
$validator = Validator::make($request->all(), [
    'role' => 'required|in:user,admin,editor',
    'permissions' => function ($attribute, $value, $fail) use ($request) {
        if ($request->input('role') !== 'admin' && !empty($value)) {
            $fail('Los permisos solo pueden ser asignados a administradores.');
        }
    },
]);
```

## Validación de Archivos

### Validación Básica de Archivos

```php
$validator = Validator::make($request->all(), [
    'photo' => 'required|file|max:2048',  // Máximo 2MB
    'document' => 'nullable|file|mimes:pdf,doc,docx|max:10240',  // Máximo 10MB, solo PDF o Word
]);
```

### Validación de Imágenes

```php
$validator = Validator::make($request->all(), [
    'avatar' => 'required|image|max:2048|dimensions:min_width=100,min_height=100',
    'banner' => 'nullable|image|mimes:jpeg,png|dimensions:width=1200,height=400',
]);
```

### Validación de Múltiples Archivos

```php
$validator = Validator::make($request->all(), [
    'documents' => 'required|array|min:1|max:5',
    'documents.*' => 'file|mimes:pdf|max:5120',
    
    'photos' => 'required|array|min:3|max:10',
    'photos.*' => 'image|mimes:jpeg,png|max:2048',
]);
```

## Validación de Fechas

### Formatos de Fecha

```php
$validator = Validator::make($request->all(), [
    'birth_date' => 'required|date|before:today',
    'appointment' => 'required|date|after:tomorrow',
    'start_date' => 'required|date',
    'end_date' => 'required|date|after_or_equal:start_date',
]);
```

### Validación de Fechas Relativas

```php
$validator = Validator::make($request->all(), [
    'birth_date' => 'required|date|before:-18 years',  // Al menos 18 años
    'publish_date' => 'nullable|date|after:today',  // Fecha futura
    'expiration_date' => 'required|date|after:+30 days',  // Al menos 30 días en el futuro
]);
```

## Validación del Lado del Cliente

LightWeight proporciona una forma de exportar reglas de validación al frontend:

```php
// En tu controlador
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

En tu vista:

```html
<form id="registerForm">
    <div class="form-group">
        <label for="name">Nombre</label>
        <input type="text" id="name" name="name" class="form-control">
        <div class="invalid-feedback" id="name-error"></div>
    </div>
    <!-- Otros campos... -->
    <button type="submit">Registrar</button>
</form>

<script>
    const validationRules = @json($validationRules);
    
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Usar las reglas exportadas con tu biblioteca de validación JS favorita
        const validator = new Validator(formData, validationRules);
        
        if (validator.fails()) {
            const errors = validator.errors();
            // Mostrar errores en el formulario
        } else {
            // Enviar formulario
            this.submit();
        }
    });
</script>
```

## Extensibilidad

### Creación de Reglas Personalizadas

```php
// Creando una Regla como una Clase
namespace App\Rules;

use LightWeight\Validation\Contracts\Rule;

class PhoneNumber implements Rule
{
    protected $countryCode;
    
    public function __construct($countryCode = 'ES')
    {
        $this->countryCode = $countryCode;
    }
    
    public function validate($attribute, $value, $fail)
    {
        if ($this->countryCode === 'ES') {
            // Validación para números españoles
            if (!preg_match('/^(\+34|0034|34)?[6-9]\d{8}$/', $value)) {
                $fail('El campo :attribute debe ser un número de teléfono español válido.');
            }
        } elseif ($this->countryCode === 'US') {
            // Validación para números estadounidenses
            if (!preg_match('/^(\+1|001|1)?[2-9]\d{2}[2-9]\d{6}$/', $value)) {
                $fail('El campo :attribute debe ser un número de teléfono estadounidense válido.');
            }
        } else {
            // Validación genérica
            if (!preg_match('/^\+?[0-9]{10,15}$/', $value)) {
                $fail('El campo :attribute debe ser un número de teléfono válido.');
            }
        }
    }
}
```

Uso de la regla personalizada:

```php
$validator = Validator::make($request->all(), [
    'phone' => ['required', new PhoneNumber('ES')],
    'us_contact' => ['nullable', new PhoneNumber('US')],
]);
```

### Registro Global de Reglas

```php
// En un proveedor de servicios
public function boot()
{
    Validator::extend('phone', function ($attribute, $value, $parameters, $validator) {
        $countryCode = $parameters[0] ?? 'ES';
        
        if ($countryCode === 'ES') {
            return preg_match('/^(\+34|0034|34)?[6-9]\d{8}$/', $value);
        } elseif ($countryCode === 'US') {
            return preg_match('/^(\+1|001|1)?[2-9]\d{2}[2-9]\d{6}$/', $value);
        }
        
        return preg_match('/^\+?[0-9]{10,15}$/', $value);
    });
    
    Validator::extendImplicit('password_strength', function ($attribute, $value, $parameters, $validator) {
        $minScore = $parameters[0] ?? 3;
        
        // Implementación simplificada de puntuación de contraseñas
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

Uso de reglas registradas globalmente:

```php
$validator = Validator::make($request->all(), [
    'phone' => 'required|phone:ES',
    'password' => 'required|min:8|password_strength:4',
]);
```

## Validación Avanzada

### Validación por Pasos

```php
// Paso 1: Datos personales
public function storeStep1(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'phone' => 'required|string|max:20',
    ]);
    
    // Guardar en sesión para uso posterior
    session(['registration.step1' => $validated]);
    
    return redirect('/register/step2');
}

// Paso 2: Dirección
public function storeStep2(Request $request)
{
    $validated = $request->validate([
        'address' => 'required|string|max:255',
        'city' => 'required|string|max:100',
        'zip' => 'required|string|max:10',
    ]);
    
    // Guardar en sesión para uso posterior
    session(['registration.step2' => $validated]);
    
    return redirect('/register/step3');
}

// Paso 3: Finalización
public function storeStep3(Request $request)
{
    $validated = $request->validate([
        'password' => 'required|min:8|confirmed',
        'terms' => 'required|accepted',
    ]);
    
    // Combinar todos los datos de los pasos
    $userData = array_merge(
        session('registration.step1', []),
        session('registration.step2', []),
        $validated
    );
    
    // Crear usuario
    $user = User::create($userData);
    
    // Limpiar datos de sesión
    session()->forget(['registration.step1', 'registration.step2']);
    
    // Iniciar sesión y redirigir
    auth()->login($user);
    
    return redirect('/dashboard');
}
```

### Validación de Modelos

```php
// En un modelo
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

// En un controlador
public function update(Request $request, $id)
{
    $user = User::find($id);
    $user->fill($request->all());
    
    try {
        $user->validate()->save();
        return redirect('/users')->with('success', 'Usuario actualizado correctamente');
    } catch (ValidationException $e) {
        return back()->withErrors($e->validator)->withInput();
    }
}
```

### Validación de Formularios Dinámicos

```php
public function store(Request $request)
{
    // Validación básica
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
    ]);
    
    // Validar campos dinámicos según el tipo de usuario
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
    
    // Validar diferentes campos según el país
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
    
    // Continuar con el procesamiento
    User::create($validator->validated());
    
    return redirect('/users')->with('success', 'Usuario creado correctamente');
}
```

## Buenas Prácticas

### Separación de la Lógica de Validación

```php
// En un archivo separado, por ejemplo, App\Validators\UserValidator.php
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
            'name.required' => 'El nombre es obligatorio.',
            'email.unique' => 'Este correo ya está registrado.',
            'role.in' => 'El rol seleccionado no es válido.',
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

// En el controlador
public function store(Request $request)
{
    $validator = UserValidator::validateForCreate($request->all());
    
    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }
    
    User::create($validator->validated());
    
    return redirect('/users')->with('success', 'Usuario creado correctamente');
}

public function update(Request $request, $id)
{
    $validator = UserValidator::validateForUpdate($request->all(), $id);
    
    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }
    
    $user = User::find($id);
    $user->update($validator->validated());
    
    return redirect('/users')->with('success', 'Usuario actualizado correctamente');
}
```

### Reutilización de Reglas

```php
// En un trait
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

// En un controlador
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
        
        return redirect('/products')->with('success', 'Producto creado correctamente');
    }
}
```

### Uso de Form Request para Validación

```php
// En un Form Request personalizado
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
            'name.required' => 'El nombre es obligatorio.',
            'email.unique' => 'Este correo ya está registrado.',
            'role.in' => 'El rol seleccionado no es válido.',
        ];
    }
    
    public function attributes()
    {
        return [
            'name' => 'nombre completo',
            'email' => 'dirección de correo',
            'password' => 'contraseña',
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

// En el controlador
public function store(StoreUserRequest $request)
{
    // La validación ya se ha realizado
    $user = User::create($request->validated());
    
    return redirect('/users')->with('success', 'Usuario creado correctamente');
}
```

## Conclusión

El sistema de validación de LightWeight proporciona una forma robusta y flexible de validar datos de entrada en tu aplicación. Con su amplia variedad de reglas predefinidas y su capacidad para extenderse con reglas personalizadas, puedes asegurar que solo los datos válidos sean procesados por tu aplicación.

La separación de la lógica de validación en clases específicas o Form Requests permite una mayor reutilización y mantenibilidad del código. Además, la posibilidad de exportar reglas al frontend facilita la implementación de una validación coherente tanto en el servidor como en el cliente.

Utiliza estas herramientas para mantener la integridad de tus datos y proporcionar una mejor experiencia de usuario al mostrar mensajes de error claros y útiles.

> 🌐 [English Documentation](../en/validation-guide.md)
