# Inventory tracker

App to track items in inventory, results are divided by pages, number of items per page can be set from config file.
Items can be ordered by name, quantity, price, mod-date, add-date, default order is add-date and it can also be changed from config file.
It is possible to search for items and have same order filters applied. Items can be added or edited from every window.

App tracks price, quantity, name, picture, date of addition and date of modification of added item.
Uploaded pictures are given random names to avoid duplicating names, it is possible to remove picture from update panel, after it picture is also removed from server.
That also applies to deleting item. If picture is removed from update panel default picture takes its place.

This project is free to copy or use however you want, please use it as base and build something more complex.

## Getting Started

Clone this repository:

```
git clone https://github.com/nenadfilipovic/inventory-tracker
```

### Prerequisites

Create tables with this code in your PHP MySQL manager to store items and users.

```sql
CREATE TABLE lager (
id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(255) NOT NULL,
quantity INT(6) NOT NULL,
price INT(6) NOT NULL,
image VARCHAR(255) NOT NULL,
mod_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
add_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
)
```

```sql
CREATE TABLE users (
id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
username VARCHAR(255) NOT NULL,
password VARCHAR(255) NOT NULL,
email VARCHAR(255) NOT NULL,
type VARCHAR(255) NOT NULL,
mod_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
add_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
)
```

### Installing

After setting up database deploy project to your php supported hosting.

## Running the tests

-

### Break down into end to end tests

-

### And coding style tests

-

## Deployment

Upload code via sftp or whatever.

## Built With

* [PHP](https://www.php.net/)
* [JavaScript](https://www.ecma-international.org/)

## Authors

* **Nenad Filipovic** - *Initial work* - [nenadfilipovic](https://github.com/nenadfilipovic)

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Acknowledgments

-
