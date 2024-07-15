# T9 Phonebook 

T9 phonebook application to manage contacts and use a T9 search functionality.

## Table of Contents

- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Usage](#usage)
- [Testing](#testing)
- [Features](#features)
- [Other Application Features](#other-application-features)


## Prerequisites

Before you begin, please ensure that you have the following requirements installed on your system:
- PHP 8.0 or higher
- Composer
- MySQL

## Installation

1. **Clone the repository:**

    ```sh
   git clone https://github.com/Aneesa18/T9Phonebook.git
   cd T9Phonebook
   ```

2. **Install dependencies:**

    ```sh
    composer install
   ```

3. **Update env variables:**

    - Copy .env.dist to .env 
    - Update the .env variables based on your system. For example, update DB_USER to the username of your mysql.

## Usage

1. **Start the server:**
   
   You can start the server using:

    ```sh
    php -S localhost:8000 -t public
    ```

2. **Access the application:**

   Open your browser and go to [http://localhost:8000](http://localhost:8000).

## Testing

To run all the tests use:

```sh
vendor/bin/phpunit
```

To run only the PhonebookTest use:

```sh
vendor/bin/phpunit tests/Phonebook/Unit/PhonebookTest.php
```

## Features

- Adding contacts: 

    - Contacts can be added to the database by entering the first name, the last name and the phone number of the contact.
    - All three fields are required.
    - Phone number can only be numeric and can include symbols +, (, ), -

- Search contacts: 

  - Contacts can be searched using a sequence of numbers in the search query.
  - Search query can only be numeric.
  - The contacts that match the search query will be displayed in a table.
  - The result entries are paginated with 10 entries per page.
  - Use the previous, next buttons to scroll through the result pages. Directly the page numbers can also be clicked to navigate.

- Database creation:

   - Database and table creations are done automatically once the server is started.
  
- Rate limit:

  - For security reasons, only 100 requests per user are accepted in an hour.

## Other Application Features

Support for large number of data records:

   - To ensure high performance regardless of the number of data records, pagination is introduced.
   - Using pagination, the entire dataset does not have to be processed all at once but in small chunks.
   - This reduces the load on the server and the database and ensures faster loading pages.
   - This also supports user experience by providing ease of navigation.

Application security:

   1. Injection attacks prevention:
     
      - Before processing the input values, it is ensured that they meet the expected formats and constraints using validation.
      - Possible harmful characters are removed from the input using sanitization.
      - These features help in preventing injection attacks.

   2. SQL injection prevention:
      
      - Prepared SQL statements are used with parameterized queries to protect against SQL injection.

   3. Cross site scripting protection:

      - Ensuring all the user input is properly encoded before outputting it to the browser to prevent XSS attacks.

   4. Error handling:

      - Handling errors to avoid giving detailed messages to the users about the application.
   
   5. Brute force attacks prevention:

      - To prevent abuse of the application, rate limit is used. So that one user can only send a limited number of requests in an hour. 
      - This feature not only helps against brute force attacks on search query but also other things like Denial of Service, bot scraping, parameter tampering etc.,
