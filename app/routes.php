<?php


Route::get('/practice-creating', function() {

    # Instantiate a new Book model class
    $book = new Book();

    # Set 
    $book->title = 'The Great Gatsby';
    $book->author = 'F. Scott Fiztgerald';
    $book->published = 1925;
    $book->cover = 'http://img2.imagesbn.com/p/9780743273565_p0_v4_s114x166.JPG';
    $book->purchase_link = 'http://www.barnesandnoble.com/w/the-great-gatsby-francis-scott-fitzgerald/1116668135?ean=9780743273565';

    # This is where the Eloquent ORM magic happens
    $book->save();

    return 'A new book has been added! Check your database to see...';

});


Route::get('/practice-reading', function() {

    # The all() method will fetch all the rows from a Model/table
    $books = Book::all();

    # Make sure we have results before trying to print them...
    if($books->isEmpty() != TRUE) {

        # Typically we'd pass $books to a View, but for quick and dirty demonstration, let's just output here...
        foreach($books as $book) {
            echo $book->title.'<br>';
        }
    }
    else {
        return 'No books found';
    }

});


Route::get('/practice-updating', function() {

    # First get a book to update
    $book = Book::where('author', 'LIKE', '%Scott%')->first();

    # If we found the book, update it
    if($book) {

        # Give it a different title
        $book->title = 'The Really Great Gatsby';

        # Save the changes
        $book->save();

        return "Update complete; check the database to see if your update worked...";
    }
    else {
        return "Book not found, can't update.";
    }

});


Route::get('/practice-deleting', function() {

    # First get a book to delete
    $book = Book::where('author', 'LIKE', '%Scott%')->first();

    # If we found the book, delete it
    if($book) {

        # Goodbye!
        $book->delete();

        return "Deletion complete; check the database to see if it worked...";

    }
    else {
        return "Can't delete - Book not found.";
    }

});


# /app/routes.php
Route::get('/debug', function() {

    echo '<pre>';

    echo '<h1>environment.php</h1>';
    $path   = base_path().'/environment.php';

    try {
        $contents = 'Contents: '.File::getRequire($path);
        $exists = 'Yes';
    }
    catch (Exception $e) {
        $exists = 'No. Defaulting to `production`';
        $contents = '';
    }

    echo "Checking for: ".$path.'<br>';
    echo 'Exists: '.$exists.'<br>';
    echo $contents;
    echo '<br>';

    echo '<h1>Environment</h1>';
    echo App::environment().'</h1>';

    echo '<h1>Debugging?</h1>';
    if(Config::get('app.debug')) echo "Yes"; else echo "No";

    echo '<h1>Database Config</h1>';
    print_r(Config::get('database.connections.mysql'));

    echo '<h1>Test Database Connection</h1>';
    try {
        $results = DB::select('SHOW DATABASES;');
        echo '<strong style="background-color:green; padding:5px;">Connection confirmed</strong>';
        echo "<br><br>Your Databases:<br><br>";
        print_r($results);
    } 
    catch (Exception $e) {
        echo '<strong style="background-color:crimson; padding:5px;">Caught exception: ', $e->getMessage(), "</strong>\n";
    }

    echo '</pre>';

});


// Homepage
Route::get('/', function() {
    
    return View::make('index');

});


// List all books / search
Route::get('/list/{format?}', function($format = 'html') {

    $query = Input::get('query');

    $library = new Library();
    $library->setPath(app_path().'/database/books.json');
    $books = $library->getBooks();

    if($query) {
        $books = $library->search($query);
    }

    if($format == 'json') {
        return 'JSON Version';
    }
    elseif($format == 'pdf') {
        return 'PDF Version;';
    }
    else {
        return View::make('list')
            ->with('name','Susan')
            ->with('books', $books)
            ->with('query', $query);

    }

});


// Display the form for a new book
Route::get('/add', function() {

    return View::make('add');

});

// Process form for a new book
Route::post('/add', function() {


});


// Display the form to edit a book
Route::get('/edit/{title}', function() {


});

// Process form for a edit book
Route::post('/edit/', function() {


});


// Test route to load and output books
Route::get('/data', function() {

    $library = new Library();

    $library->setPath(app_path().'/database/books.json');
    
    $books = $library->getBooks();

    // Return the file
    echo Pre::render($books);

});




/*-------------------------------------------------------------------------------------------------
All debugging and testing routes go below here...
-------------------------------------------------------------------------------------------------*/

/* 
Test to make sure we can connect to MySQL
*/
Route::get('mysql-test', function() {

    # Print environment
    echo 'Environment: '.App::environment().'<br>';

    # Use the DB component to select all the databases
    $results = DB::select('SHOW DATABASES;');

    # If the "Pre" package is not installed, you should output using print_r instead
    echo Pre::render($results);

});


/* 
When testing environments you can use this route to trigger an error to see what your debugging settings are doing.
*/
Route::get('/trigger-error',function() {

    # Class Foobar should not exist, so this should create an error
    $foo = new Foobar;

});




/* 
The best way to fill your tables with sample/test data is using Laravel's Seeding feature.
Before we get to that, though, here's a quick-and-dirty practice route that will
throw three books into the `books` table.
*/
Route::get('/seed', function() {

    # Build the raw SQL query
    $sql = "INSERT INTO books (author,title,published,cover,purchase_link) VALUES 
            ('F. Scott Fitzgerald','The Great Gatsby',1925,'http://img2.imagesbn.com/p/9780743273565_p0_v4_s114x166.JPG','http://www.barnesandnoble.com/w/the-great-gatsby-francis-scott-fitzgerald/1116668135?ean=9780743273565'),
            ('Sylvia Plath','The Bell Jar',1963,'http://img1.imagesbn.com/p/9780061148514_p0_v2_s114x166.JPG','http://www.barnesandnoble.com/w/bell-jar-sylvia-plath/1100550703?ean=9780061148514'),
            ('Maya Angelou','I Know Why the Caged Bird Sings',1969,'http://img1.imagesbn.com/p/9780345514400_p0_v1_s114x166.JPG','http://www.barnesandnoble.com/w/i-know-why-the-caged-bird-sings-maya-angelou/1100392955?ean=9780345514400')
            ";

    # Run the SQL query
    echo DB::statement($sql);

    # Get all the books just to test it worked
    $books = DB::table('books')->get();

    # Print all the books
    echo Paste\Pre::render($books,'');

});




/*
Quick way to clear out the books table when we've been messing it up with lots of test data
*/
Route::get('/truncate', function() {

   Book::truncate();

   echo 'The book table was emptied.';

});




/*
Print all available routes
*/
Route::get('/routes', function() {
    
    $routeCollection = Route::getRoutes();

    foreach($routeCollection as $value) {
        echo "<a href='/".$value->getPath()."' target='_blank'>".$value->getPath()."</a><br>";
    }

});












