<?php

namespace LightWeight\Tests\Database\ORM;

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;
use LightWeight\Tests\Database\RefreshDatabase;
use LightWeight\Database\Contracts\DatabaseDriverContract;
use LightWeight\Database\ORM\Model;

/**
 * Model class with custom attributes
 */
class CustomModel extends Model
{
    protected ?string $table = "custom_models";
    protected string $primaryKey = "custom_id";
    protected array $fillable = ['title', 'description'];
    protected array $hidden = ['secret_field'];
    protected bool $insertTimestamps = false;
}

/**
 * Model class with hidden attributes
 */
class HiddenAttributesModel extends Model
{
    protected ?string $table = "hidden_models";
    protected array $fillable = ['name', 'password', 'api_token'];
    protected array $hidden = ['password', 'api_token'];
}

class ModelExtendedTest extends TestCase
{
    use RefreshDatabase;

    protected ?DatabaseDriverContract $driver = null;

    private function createTableWithCustomPrimaryKey()
    {
        $this->driver->statement("
            CREATE TABLE custom_models (
                custom_id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(256),
                description VARCHAR(256),
                secret_field VARCHAR(256)
            )
        ");
    }

    private function createHiddenAttributesTable()
    {
        $this->driver->statement("
            CREATE TABLE hidden_models (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(256),
                password VARCHAR(256),
                api_token VARCHAR(256),
                created_at DATETIME,
                updated_at DATETIME NULL
            )
        ");
    }

    public function testCustomPrimaryKey()
    {
        $this->createTableWithCustomPrimaryKey();
        
        // Create using the model
        $model = CustomModel::create([
            'title' => 'Custom Model',
            'description' => 'Testing custom primary key',
            'secret_field' => 'SECRET_DATA'
        ]);
        
        $this->assertInstanceOf(CustomModel::class, $model);
        $this->assertNotNull($model->custom_id);
        
        // Check what was actually saved in the database
        $rawData = $this->driver->statement("SELECT * FROM custom_models WHERE custom_id = ?", [$model->custom_id]);
        $this->assertCount(1, $rawData);
        $this->assertEquals('Custom Model', $rawData[0]['title']);
        $this->assertEquals('Testing custom primary key', $rawData[0]['description']);
        
        // Now let's see if find() returns the correct data
        $foundModel = CustomModel::find($model->custom_id);
        $this->assertInstanceOf(CustomModel::class, $foundModel);
        $this->assertEquals('Custom Model', $foundModel->title);
        $this->assertEquals('Testing custom primary key', $foundModel->description);
    }
    
    public function testCustomTimestampSetting()
    {
        $this->createTableWithCustomPrimaryKey();
        
        $model = new CustomModel();
        $model->title = 'No Timestamps';
        $model->description = 'This model should not have timestamps';
        $model->save();
        
        $rows = $this->driver->statement("SELECT * FROM custom_models");
        $this->assertCount(1, $rows);
        
        // CustomModel has timestamps disabled, so they shouldn't appear in DB
        $this->assertArrayNotHasKey('created_at', $rows[0]);
        $this->assertArrayNotHasKey('updated_at', $rows[0]);
    }
    
    public function testHiddenAttributes()
    {
        $this->createHiddenAttributesTable();
        
        // Create using the model
        $model = HiddenAttributesModel::create([
            'name' => 'Secure User',
            'password' => 'secret123',
            'api_token' => 'abc123xyz789'
        ]);
        
        // Hidden attributes should be in the database
        $dbRow = $this->driver->statement("SELECT * FROM hidden_models WHERE id = ?", [$model->id])[0];
        $this->assertEquals('secret123', $dbRow['password']);
        $this->assertEquals('abc123xyz789', $dbRow['api_token']);
        
        // Hidden attributes should be excluded from toArray()
        $array = $model->toArray();
        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('api_token', $array);
        $this->assertArrayHasKey('name', $array);
        
        // Hidden attributes should be excluded from JSON
        $json = json_encode($model);
        $data = json_decode($json, true);
        $this->assertArrayNotHasKey('password', $data);
        $this->assertArrayNotHasKey('api_token', $data);
        $this->assertArrayHasKey('name', $data);
    }
    
    public function testQueryMethodChaining()
    {
        $this->createHiddenAttributesTable();
        
        // Create test data using models
        HiddenAttributesModel::create([
            'name' => 'User One',
            'password' => 'pass1',
            'api_token' => 'token1'
        ]);
        
        HiddenAttributesModel::create([
            'name' => 'User Two',
            'password' => 'pass2',
            'api_token' => 'token2'
        ]);
        
        // Test chaining methods
        $results = HiddenAttributesModel::where('name', 'LIKE', 'User%')
            ->orderBy('name', 'desc')
            ->limit(1)
            ->get();
        
        $this->assertCount(1, $results);
        $this->assertEquals('User Two', $results[0]->name);
        
        // Test more complex query
        HiddenAttributesModel::create([
            'name' => 'Another User',
            'password' => 'pass3',
            'api_token' => 'token3'
        ]);
        
        $count = HiddenAttributesModel::query()
            ->where('name', 'LIKE', 'User%')
            ->count();
        
        $this->assertEquals(2, $count);
    }
    
    public function testBasicCRUDOperations()
    {
        $this->createHiddenAttributesTable();
        
        // Create
        $model = HiddenAttributesModel::create([
            'name' => 'CRUD Test',
            'password' => 'testpass',
            'api_token' => 'testtoken'
        ]);
        
        $this->assertInstanceOf(HiddenAttributesModel::class, $model);
        $this->assertEquals('CRUD Test', $model->name);
        
        // Read
        $found = HiddenAttributesModel::find($model->id);
        $this->assertInstanceOf(HiddenAttributesModel::class, $found);
        $this->assertEquals('CRUD Test', $found->name);
        
        // Update
        $found->name = 'Updated CRUD Test';
        $found->update();
        
        $updated = HiddenAttributesModel::find($model->id);
        $this->assertEquals('Updated CRUD Test', $updated->name);
        
        // Delete
        $updated->delete();
        
        $afterDelete = HiddenAttributesModel::find($model->id);
        $this->assertNull($afterDelete);
        
        $count = $this->driver->statement("SELECT COUNT(*) as count FROM hidden_models")[0]['count'];
        $this->assertEquals(0, $count);
    }
}
