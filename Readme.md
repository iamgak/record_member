# RecordMembers

Record Members to manage Teams Record using PHP, MySQL, DataTable, AJAX, fetch. Created RESTful APIs to perform CRUD operation with proper file structure and server side validation and Interactive Graphical Interface.

## Technologies Used
- PHP: Backend development
- MySQL: Database Management

## Getting Started

### Prerequisites
- PHP & MySQL installed on your system. 

### Installation
1. Clone the repository:
    ```bash
    git clone github.com/iamgak/record_member
    ```

2. To Add all the Schema use db.sql file 

3. Navigate to the project directory:

    ```bash
    cd record_member/public
    ```

4. Run the script:

    ```bash
    php -S localhost:8080
    ```
5. To Seed Default Data GET http://localhost:8080/seed 

6. Now you can use browser to perform CRUD operations

### Working

Here, on page load browser will send a GET request to server on http://localhost:8080/fetch to get all the user's necessary information and than DataTable will print on the basis of limit and public/assets/js/index.js configuration basis. If you are working with two different port you have define CORS Header on serverside(in PHP code /public/index.php) to start communication.

## Contributing
Contributions are welcome! If you'd like to contribute to this project, please fork the repository and submit a pull request with your changes.

