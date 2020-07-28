<?php

// Load server code
include('server.php');

// Force user to log in
if (!isLoggedIn()) {
    $_SESSION['message'] = "You must be logged in first!";
    header('location: login.php');
    exit();
}

// Export needed data
if (isSearch()) {
    $query = $_GET["q"];
    list($total_pages, $page, $order, $result) = getQueryItems($query, $_GET['o']);
    $action = 'index.php?q=' . $query . '&p=' . $page . '&o=' . $order;
} elseif (isSingle()) {
    list(, , , $result) = getQueryItems($_GET['id'], $_GET['o']);
    $action = 'index.php?id=' . $_GET['id'];
} else {
    list($total_pages, $page, $order, $result) = getItems($_GET['o']);
    $action = 'index.php?p=' . $page . '&o=' . $order;
}

// Update, View, Add, Order, Delete GET request handler
if (isset($_GET['update'])) {
    $update = true;
    $id = $_GET['update'];
    list($name, $quantity, $price, $image) = getItem($id);
}
if (isset($_GET['view'])) {
    $view = true;
    $id = $_GET['view'];
    list($name, $quantity, $price, $image) = getItem($id);
}
if (isset($_GET['add'])) {
    $add = true;
}
if (isset($_GET['o'])) {
    $order = $_GET['o'];
    if (isSearch()) {
        list($total_pages, $page, $order, $result) = getQueryItems($query, $order);
    } else {
        list($total_pages, $page, $order, $result) = getItems($order);
    }
}
if (isset($_GET['delete'])) {
    $delete = true;
    $id = $_GET['delete'];
}
if (isset($_GET['imgrem'])) {
    $id = $_GET['imgrem'];
    imageRemove($id);
}
?>
<!-- HTML -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?php
    if (isSearch()) {
        echo '<title>Search</title>';
    } else {
        echo '<title>Inventory</title>';
    }
    ?>
    <meta name="author" content="Nenad Filipovic">
    <meta name="description" content="Website for maintaining inventory.">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico"/>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <meta name="robots" content="noindex,nofollow">
</head>
<body>
<div class="container">
    <!-- Logo -->
    <h1>INVENTORY</h1>
    <!-- Navigation -->
    <nav>
        <a href="#" onclick="toggleSearch()"><img src="img/search.png" alt="Search"/>
            <p>Search</p></a>
        <a href="index.php"><img src="img/home.png" alt="Search"/>
            <p>Home</p></a>
        <a href="<?php echo $action; ?>&add"><img src="img/add.png" alt="Add"/>
            <p>Add new</p></a>
    </nav>
    <a href="?logout">sad</a>
    <!-- Get all columns from database -->
    <?php
    $query_columns = mysqli_query($db, "SHOW COLUMNS FROM lager");
    ?>
    <div class="order-menu">
        <p>Order by:</p>
        <?php while ($items = mysqli_fetch_array($query_columns)) {
            $array[] = $items['Field'];
        }
        unset($array['0'], $array['4']);
        foreach ($array as $item) {
            if (isSingle()) { ?>
                <p><?php echo str_replace('_', ' ', $item); ?></p>
            <?php } else {
                if ($item === $order) { ?>
                    <p><?php echo str_replace('_', ' ', $item); ?></p>
                <?php } else {
                    if (isSearch()) { ?>
                        <a href="index.php?q=<?php echo $query; ?>&p=<?php echo $page; ?>&o=<?php echo $item; ?>"><p><?php echo str_replace('_', ' ', $item); ?></p></a>
                    <?php } else { ?>
                        <a href="index.php?p=<?php echo $page; ?>&o=<?php echo $item; ?>"><p><?php echo str_replace('_', ' ', $item); ?></p></a>
                    <?php }
                }
            }
        } ?>
    </div>
    <!-- Alert message -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="message">
            <span>
                <?php
                echo $_SESSION['message'];
                unset($_SESSION['message']);
                ?>
            </span>
        </div>
    <?php endif ?>
    <!-- Search field -->
    <div class="search-form">
        <form autocomplete="off" action="index.php" method="get">
            <label for="search"><span>Search</span></label>
            <input id="search" required type="text" name="q" placeholder="Search for">
            <div class="buttons">
                <button type="submit"><span>GO</span></button>
                <button onclick="toggleSearch()" type="button"><span>CANCEL</span></button>
            </div>
        </form>
    </div>
    <!-- Header -->
    <?php
    if (isSearch()) { ?>
        <p>Search result</p>
    <?php } else { ?>
        <p>Available items</p>
    <?php } ?>
    <!-- Display data -->
    <div class="results">
        <div class="header">
            <p>Name</p>
            <p>Quantity</p>
            <p>Price</p>
            <p>Image</p>
            <p>Mod Date</p>
            <p>Add Date</p>
            <p>Modify</p>
            <p>Delete</p>
        </div>
        <?php while ($data = mysqli_fetch_array($result)) { ?>
            <div class="content">
                <a href="<?php echo $action; ?>&view=<?php echo $data['id']; ?>"><p><?php echo $data['name']; ?></p></a>
                <p><?php echo $data['quantity']; ?></p>
                <p><?php echo $data['price']; ?></p>
                <img class="main-image" src="<?php echo $data['image']; ?>" alt="Image">
                <p><?php echo $data['mod_date']; ?></p>
                <p><?php echo $data['add_date']; ?></p>
                <a href="<?php echo $action; ?>&update=<?php echo $data['id']; ?>"><span>Update</span></a>
                <a href="<?php echo $action; ?>&delete=<?php echo $data['id']; ?>"><span>Delete</span></a>
            </div>
        <?php } ?>
    </div>
    <!-- Pagination -->
    <!-- Default pagination -->
    <?php if (!isSearch()) { ?>
        <div class="pagination-container">
            <?php if (ceil($total_pages / $items_per_page) > 0): ?>
                <ul class="pagination">
                    <?php if ($page > 1): ?>
                        <li class="prev"><a href="index.php?p=<?php echo $page - 1; ?>&o=<?php echo $order; ?>"><span>Prev</span></a></li><?php endif; ?>
                    <?php if ($page > 3): ?>
                        <li class="start"><a href="index.php?p=1&o=<?php echo $order; ?>"><span>1</span></a></li>
                        <li class="dots"><span>...</span></li><?php endif; ?>
                    <?php if ($page - 2 > 0): ?>
                        <li class="page"><a href="index.php?p=<?php echo $page - 2; ?>&o=<?php echo $order; ?>"><span><?php echo $page - 2 ?></span></a></li><?php endif; ?>
                    <?php if ($page - 1 > 0): ?>
                        <li class="page"><a href="index.php?p=<?php echo $page - 1; ?>&o=<?php echo $order; ?>"><span><?php echo $page - 1; ?></span></a></li><?php endif; ?>
                    <li class="currentpage">
                        <a href="index.php?p=<?php echo $page; ?>&o=<?php echo $order; ?>"><span><?php echo $page ?></span></a></li>
                    <?php if ($page + 1 < ceil($total_pages / $items_per_page) + 1): ?>
                        <li class="page"><a href="index.php?p=<?php echo $page + 1; ?>&o=<?php echo $order; ?>"><span><?php echo $page + 1; ?></span></a></li><?php endif; ?>
                    <?php if ($page + 2 < ceil($total_pages / $items_per_page) + 1): ?>
                        <li class="page"><a href="index.php?p=<?php echo $page + 2; ?>&o=<?php echo $order; ?>"><span><?php echo $page + 2; ?></span></a></li><?php endif; ?>
                    <?php if ($page < ceil($total_pages / $items_per_page) - 2): ?>
                        <li class="dots">
                            <span>...</span></li>
                        <li class="end"><a href="index.php?p=<?php echo ceil($total_pages / $items_per_page) ?>&o=<?php echo $order; ?>">
                            <span><?php echo ceil($total_pages / $items_per_page) ?></span></a></li><?php endif; ?>
                    <?php if ($page < ceil($total_pages / $items_per_page)): ?>
                        <li class="next"><a href="index.php?p=<?php echo $page + 1; ?>&o=<?php echo $order; ?>">
                            <span>Next</span></a></li><?php endif; ?>
                </ul>
            <?php endif; ?>
        </div>
    <?php } else { ?>
        <!-- Query pagination -->
        <div class="pagination-container">
            <?php if (ceil($total_pages / $items_per_page) > 0): ?>
                <ul class="pagination">
                    <?php if ($page > 1): ?>
                        <li class="prev"><a href="index.php?q=<?php echo $query; ?>&p=<?php echo $page - 1; ?>&o=<?php echo $order; ?>"><span>Prev</span></a></li><?php endif; ?>
                    <?php if ($page > 3): ?>
                        <li class="start"><a href="index.php?q=<?php echo $query; ?>&p=1&o=<?php echo $order; ?>"><span>1</span></a></li>
                        <li class="dots"><span>...</span></li><?php endif; ?>
                    <?php if ($page - 2 > 0): ?>
                        <li class="page"><a href="index.php?q=<?php echo $query; ?>&p=<?php echo $page - 2; ?>&o=<?php echo $order; ?>"><span><?php echo $page - 2 ?></span></a></li><?php endif; ?>
                    <?php if ($page - 1 > 0): ?>
                        <li class="page"><a href="index.php?q=<?php echo $query; ?>&p=<?php echo $page - 1; ?>&o=<?php echo $order; ?>"><span><?php echo $page - 1; ?></span></a></li><?php endif; ?>
                    <li class="currentpage">
                        <a href="index.php?q=<?php echo $query; ?>&p=<?php echo $page; ?>&o=<?php echo $order; ?>"><span><?php echo $page ?></span></a></li>
                    <?php if ($page + 1 < ceil($total_pages / $items_per_page) + 1): ?>
                        <li class="page"><a href="index.php?q=<?php echo $query; ?>&p=<?php echo $page + 1; ?>&o=<?php echo $order; ?>"><span><?php echo $page + 1; ?></span></a></li><?php endif; ?>
                    <?php if ($page + 2 < ceil($total_pages / $items_per_page) + 1): ?>
                        <li class="page"><a href="index.php?q=<?php echo $query; ?>&p=<?php echo $page + 2; ?>&o=<?php echo $order; ?>"><span><?php echo $page + 2; ?></span></a></li><?php endif; ?>
                    <?php if ($page < ceil($total_pages / $items_per_page) - 2): ?>
                        <li class="dots">
                            <span>...</span></li>
                        <li class="end"><a href="index.php?q=<?php echo $query; ?>&p=<?php echo ceil($total_pages / $items_per_page) ?>&o=<?php echo $order; ?>">
                            <span><?php echo ceil($total_pages / $items_per_page) ?></span></a></li><?php endif; ?>
                    <?php if ($page < ceil($total_pages / $items_per_page)): ?>
                        <li class="next"><a href="index.php?q=<?php echo $query; ?>&p=<?php echo $page + 1; ?>&o=<?php echo $order; ?>">
                            <span>Next</span></a></li><?php endif; ?>
                </ul>
            <?php endif; ?>
        </div>
    <?php } ?>
    <!-- Update item -->
    <?php if ($update == true): ?>
        <div class="modal">
            <div class="modal-content">
                <div class="update-form">
                    <form autocomplete="off" method="post" action="<?php echo $action ?>" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                        <label for="update-name">Name</label>
                        <input id="update-name" required type="text" name="name" value="<?php echo $name; ?>">
                        <label for="update-quantity">Quantity</label>
                        <input id="update-quantity" required type="number" min="1" name="quantity" value="<?php echo $quantity; ?>">
                        <label for="update-price">Price</label>
                        <input id="update-price" required type="number" min="1" name="price" value="<?php echo $price; ?>">
                        <label>Image</label>
                        <div class="upload-form">
                            <img id="image" src="<?php echo $image; ?>" alt="">
                            <input id="files" accept="image/x-png,image/gif,image/jpeg" type="file" name="image" style="display:none;">
                            <?php if ($image !== "img/default.png") { ?>
                                <a methods="post" href="<?php echo $action ?>&imgrem=<?php echo $id; ?>"><p>Remove image</p></a>
                            <?php } else { ?>
                                <label for="files"><span>Select image</span></label>
                            <?php } ?>
                        </div>
                        <div class="buttons">
                            <button type="submit" name="update"><span>Update</span></button>
                            <button type="button" onclick="toggleModal()"><span>Cancel</span></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif ?>
    <!-- View item -->
    <?php if ($view == true): ?>
        <div class="modal">
            <div class="modal-content">
                <div class="update-form">
                    <form>
                        <label for="update-name">Name</label>
                        <input id="update-name" readonly type="text" name="name" value="<?php echo $name; ?>">
                        <label for="update-quantity">Quantity</label>
                        <input id="update-quantity" readonly type="number" min="1" name="quantity" value="<?php echo $quantity; ?>">
                        <label for="update-price">Price</label>
                        <input id="update-price" readonly type="number" min="1" name="price" value="<?php echo $price; ?>">
                        <label>Image</label>
                        <div class="upload-form-modal">
                            <img id="image" src="<?php echo $image; ?>" alt="">
                        </div>
                        <div class="buttons">
                            <button type="button" onclick="toggleModal()"><span>Close</span></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif ?>
    <!-- Add item -->
    <?php if ($add == true): ?>
        <div class="modal">
            <div class="modal-content">
                <div class="add-form">
                    <form autocomplete="off" method="post" action="<?php echo $action ?>" enctype="multipart/form-data">
                        <label for="name"><span>Name</span></label>
                        <input id="name" required type="text" name="name" placeholder="Item name">
                        <label for="quantity"><span>Quantity</span></label>
                        <input id="quantity" required type="number" min="1" name="quantity" placeholder="1">
                        <label for="price"><span>Price</span></label>
                        <input id="price" required type="number" min="1" name="price" placeholder="1">
                        <label><span>Image</span></label>
                        <div class="upload-form">
                            <img id="image" src="img/default.png" alt="Image"/>
                            <input id="files" accept="image/x-png,image/gif,image/jpeg" type="file" name="image" style="display:none;">
                            <label for="files" class="select-image"><span class="text"><span>Select image</span></label>
                        </div>
                        <div class="buttons">
                            <button type="submit" name="add"><span>Save</span></button>
                            <button type="button" onclick="toggleModal()"><span>Cancel</span></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif ?>
    <!-- Delete item -->
    <?php if ($delete == true): ?>
        <div class="modal">
            <div class="modal-content">
                <div class="delete-form">
                    <form method="post" action="<?php echo $action ?>">
                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                        <p>Confirm deletion of file</p>
                        <div class="buttons">
                            <button type="submit" name="delete"><span>Delete</span></button>
                            <button type="button" onclick="toggleModal()"><span>Cancel</span></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif ?>
</div>
<script src="js/script.js"></script>
</body>
</html>