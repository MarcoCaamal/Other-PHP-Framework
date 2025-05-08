<?php

namespace LightWeight\Tests\Database\ORM;

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;
use LightWeight\Database\Contracts\DatabaseDriverContract;
use LightWeight\Database\ORM\Model;
use LightWeight\Database\ORM\Relations\BelongsTo;
use LightWeight\Database\ORM\Relations\HasMany;
use LightWeight\Database\ORM\Relations\HasOne;
use LightWeight\Tests\Database\RefreshDatabase;

/**
 * User model for testing relationships
 */
class UserModel extends Model
{
    protected ?string $table = "users";
    protected array $fillable = ['name', 'email'];

    public function profile()
    {
        return $this->hasOne(ProfileModel::class);
    }

    public function posts()
    {
        return $this->hasMany(PostModel::class);
    }

    public function comments()
    {
        return $this->hasMany(CommentModel::class);
    }
}

/**
 * Profile model for testing one-to-one relationships
 */
class ProfileModel extends Model
{
    protected ?string $table = "profiles";
    protected array $fillable = ['bio', 'website', 'user_id'];

    public function user()
    {
        return $this->belongsTo(UserModel::class);
    }
}

/**
 * Post model for testing one-to-many relationships
 */
class PostModel extends Model
{
    protected ?string $table = "posts";
    protected array $fillable = ['title', 'content', 'user_id'];

    public function user()
    {
        return $this->belongsTo(UserModel::class);
    }

    public function comments()
    {
        return $this->hasMany(CommentModel::class);
    }
}

/**
 * Comment model for testing nested relationships
 */
class CommentModel extends Model
{
    protected ?string $table = "comments";
    protected array $fillable = ['body', 'user_id', 'post_id'];

    public function user()
    {
        return $this->belongsTo(UserModel::class);
    }

    public function post()
    {
        return $this->belongsTo(PostModel::class);
    }
}

class RelationsTest extends TestCase
{
    use RefreshDatabase;
    
    protected ?DatabaseDriverContract $driver = null;
    
    /**
     * Create test tables for relationships
     */
    protected function setupTestTables()
    {
        // Create users table
        $this->driver->statement("
            CREATE TABLE users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100),
                email VARCHAR(255),
                created_at DATETIME,
                updated_at DATETIME NULL
            )
        ");
        
        // Create profiles table (one-to-one with users)
        $this->driver->statement("
            CREATE TABLE profiles (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT,
                bio TEXT,
                website VARCHAR(255),
                created_at DATETIME,
                updated_at DATETIME NULL,
                FOREIGN KEY (user_id) REFERENCES users(id)
            )
        ");
        
        // Create posts table (one-to-many with users)
        $this->driver->statement("
            CREATE TABLE posts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT,
                title VARCHAR(255),
                content TEXT,
                created_at DATETIME,
                updated_at DATETIME NULL,
                FOREIGN KEY (user_id) REFERENCES users(id)
            )
        ");
        
        // Create comments table (one-to-many with posts and users)
        $this->driver->statement("
            CREATE TABLE comments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                post_id INT,
                user_id INT,
                body TEXT,
                created_at DATETIME,
                updated_at DATETIME NULL,
                FOREIGN KEY (post_id) REFERENCES posts(id),
                FOREIGN KEY (user_id) REFERENCES users(id)
            )
        ");
    }
    
    /**
     * Create test data for the relationship tests
     */
    protected function createTestData()
    {
        // Create a user
        $user1 = UserModel::create([
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);
        
        $user2 = UserModel::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com'
        ]);
        
        // Create a profile for the first user
        $profile = ProfileModel::create([
            'user_id' => $user1->id,
            'bio' => 'John\'s biography',
            'website' => 'https://johndoe.com'
        ]);
        
        // Create posts for the first user
        $post1 = PostModel::create([
            'user_id' => $user1->id,
            'title' => 'First Post',
            'content' => 'Content of the first post'
        ]);
        
        $post2 = PostModel::create([
            'user_id' => $user1->id,
            'title' => 'Second Post',
            'content' => 'Content of the second post'
        ]);
        
        // Create a post for the second user
        $post3 = PostModel::create([
            'user_id' => $user2->id,
            'title' => 'Jane\'s Post',
            'content' => 'Content of Jane\'s post'
        ]);
        
        // Create comments for the posts
        CommentModel::create([
            'post_id' => $post1->id,
            'user_id' => $user2->id,
            'body' => 'Great post!'
        ]);
        
        CommentModel::create([
            'post_id' => $post1->id,
            'user_id' => $user2->id,
            'body' => 'I really enjoyed reading this'
        ]);
        
        CommentModel::create([
            'post_id' => $post2->id,
            'user_id' => $user2->id,
            'body' => 'Nice content!'
        ]);
        
        CommentModel::create([
            'post_id' => $post3->id,
            'user_id' => $user1->id,
            'body' => 'Thanks for sharing'
        ]);
    }
    
    /**
     * Test setting up relation instances
     */
    public function testRelationInstances()
    {
        $this->setupTestTables();
        
        $user = new UserModel();
        
        // Test HasOne relation
        $profileRelation = $user->profile();
        $this->assertInstanceOf(HasOne::class, $profileRelation);
        
        // Test HasMany relation
        $postsRelation = $user->posts();
        $this->assertInstanceOf(HasMany::class, $postsRelation);
        
        // Test BelongsTo relation
        $post = new PostModel();
        $userRelation = $post->user();
        $this->assertInstanceOf(BelongsTo::class, $userRelation);
    }
    
    /**
     * Test HasOne relationship
     */
    public function testHasOneRelationship()
    {
        $this->setupTestTables();
        $this->createTestData();
        
        // Get user and load profile relationship
        $user = UserModel::find(1);
        $profile = $user->profile;
        
        $this->assertInstanceOf(ProfileModel::class, $profile);
        $this->assertEquals('John\'s biography', $profile->bio);
        $this->assertEquals('https://johndoe.com', $profile->website);
        $this->assertEquals($user->id, $profile->user_id);
        
        // Call the relation again - should be cached now
        $profileAgain = $user->profile;
        $this->assertSame($profile, $profileAgain);
    }
    
    /**
     * Test HasMany relationship
     */
    public function testHasManyRelationship()
    {
        $this->setupTestTables();
        $this->createTestData();
        
        // Get user and load posts relationship
        $user = UserModel::find(1);
        $posts = $user->posts;
        
        $this->assertIsArray($posts);
        $this->assertCount(2, $posts);
        
        // Check each post belongs to the user
        foreach ($posts as $post) {
            $this->assertInstanceOf(PostModel::class, $post);
            $this->assertEquals($user->id, $post->user_id);
        }
        
        // Verify post titles
        $postTitles = array_map(fn($post) => $post->title, $posts);
        $this->assertContains('First Post', $postTitles);
        $this->assertContains('Second Post', $postTitles);
        
        // Call the relation again - should be cached now
        $postsAgain = $user->posts;
        $this->assertSame($posts, $postsAgain);
    }
    
    /**
     * Test BelongsTo relationship
     */
    public function testBelongsToRelationship()
    {
        $this->setupTestTables();
        $this->createTestData();
        
        // Get post and load user relationship
        $post = PostModel::find(1);
        $user = $post->user;
        
        $this->assertInstanceOf(UserModel::class, $user);
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertEquals(1, $user->id);
        
        // Call the relation again - should be cached now
        $userAgain = $post->user;
        $this->assertSame($user, $userAgain);
    }
    
    /**
     * Test eager loading of relationships with load() method
     */
    public function testEagerLoading()
    {
        $this->setupTestTables();
        $this->createTestData();
        
        // Get user and explicitly load profile relationship
        $user = UserModel::find(1);
        $user->load('profile');
        
        $this->assertInstanceOf(ProfileModel::class, $user->profile);
        
        // Test with getRelationValue method
        $this->assertEquals($user->profile, $user->getRelationValue('profile'));
        
        // Get post and explicitly load user relationship
        $post = PostModel::find(1);
        $post->load('user');
        
        $this->assertInstanceOf(UserModel::class, $post->user);
        
        // Test with getRelationValue method
        $this->assertEquals($post->user, $post->getRelationValue('user'));
    }
    
    /**
     * Test chained relationship queries
     */
    public function testChainedRelationshipQueries()
    {
        $this->setupTestTables();
        $this->createTestData();
        
        // Get user and posts with a condition
        $user = UserModel::find(1);
        $filteredPosts = $user->posts()->where('title', 'LIKE', '%First%')->get();
        
        $this->assertIsArray($filteredPosts);
        $this->assertCount(1, $filteredPosts);
        $this->assertEquals('First Post', $filteredPosts[0]->title);
        
        // Get post with sorted comments
        $post = PostModel::find(1);
        $sortedComments = $post->comments()->orderBy('body')->get();
        
        $this->assertIsArray($sortedComments);
        $this->assertGreaterThanOrEqual(1, count($sortedComments));
    }
    
    /**
     * Test nested relationships (loading a relationship's relationship)
     */
    public function testNestedRelationships()
    {
        $this->setupTestTables();
        $this->createTestData();
        
        // Get post, then user, then user's profile
        $post = PostModel::find(1);
        $user = $post->user;
        $profile = $user->profile;
        
        $this->assertInstanceOf(ProfileModel::class, $profile);
        $this->assertEquals('John\'s biography', $profile->bio);
        
        // Get comment, then user, then user's posts
        $comment = CommentModel::find(1);
        $commentUser = $comment->user;
        $userPosts = $commentUser->posts;
        
        $this->assertIsArray($userPosts);
    }
    
    /**
     * Test relationship methods return correct foreign and local keys
     */
    public function testRelationshipKeys()
    {
        $this->setupTestTables();
        
        $user = new UserModel();
        $profile = new ProfileModel();
        $post = new PostModel();
        
        // Test HasOne keys
        $hasOneRelation = $user->profile();
        $this->assertEquals('user_id', $hasOneRelation->getForeignKey());
        $this->assertEquals('id', $hasOneRelation->getLocalKey());
        
        // Test HasMany keys
        $hasManyRelation = $user->posts();
        $this->assertEquals('user_id', $hasManyRelation->getForeignKey());
        $this->assertEquals('id', $hasManyRelation->getLocalKey());
        
        // Test BelongsTo keys
        $belongsToRelation = $post->user();
        $this->assertEquals('user_id', $belongsToRelation->getForeignKey());
        $this->assertEquals('id', $belongsToRelation->getLocalKey());
        
        // Test with custom keys
        $customRelation = $user->hasOne(ProfileModel::class, 'custom_foreign', 'custom_local');
        $this->assertEquals('custom_foreign', $customRelation->getForeignKey());
        $this->assertEquals('custom_local', $customRelation->getLocalKey());
    }
}
