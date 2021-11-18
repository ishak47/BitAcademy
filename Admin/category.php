<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <link href="category_style.css" rel="stylesheet">
    <title>Category creator</title>
    <link rel="icon" href="../images/icon/favicon.ico" type="image/icon">
</head>
<body>
<?php
require_once "../Header/header.php";
require_once "../DB/connection.php";

session_start();

$conn = openConn();

$categoriesArray = array();
$subjectsArray = array();
$subjectsCategoriesArray = array();
$subjectWithSelectedCategoryArray = array();

//$_SESSION["selectedExistingCategory"] = null;
//$_SESSION["selectedExistingSubject"] = null;

if (isset($_SESSION["selectedExistingCategory"])) {
    $getSubjectsQuery = "select * from categories_and_subjects where categories_Category = '" . $_SESSION["selectedExistingCategory"] . "'";
} else {
    $getSubjectsQuery = "select * from categories_and_subjects";
}

$setSubjects = mysqli_query($conn, $getSubjectsQuery);

$getCategoryQuery = "select * from categories";
$setCategories = mysqli_query($conn, $getCategoryQuery);

while ($row = mysqli_fetch_assoc($setCategories)) {
    $categoriesArray[] = $row;
}

while ($row = mysqli_fetch_assoc($setSubjects)) {
    $subjectsArray[] = $row;
}

if (sizeof($categoriesArray) > 0) {
    foreach ($categoriesArray as $category) {
        $category = str_replace(" ", "_", $category);
        if (isset($_POST[$category["Category"]])) {
            $_SESSION["selectedExistingCategory"] = $_POST[$category["Category"]];

            $getSubjectsWithSelectedCategory = "select * from categories_and_subjects where categories_Category = '" . $_SESSION["selectedExistingCategory"] . "'";
            $setSubjectsWithSelectedCategory = mysqli_query($conn, $getSubjectsWithSelectedCategory);

            while ($row = mysqli_fetch_assoc($setSubjectsWithSelectedCategory)) {
                $subjectWithSelectedCategoryArray[] = $row;
            }
        }
    }
}

if (sizeof($subjectsArray) > 0) {
    foreach ($subjectsArray as $subject) {
        $subject = str_replace(" ", "_", $subject);
        if (isset($_POST[$subject["subjects_Subject"]])) {
            $_SESSION["selectedExistingSubject"] = $_POST[$subject["subjects_Subject"]];
        }
    }
}

if (!isset($_SESSION["selectedExistingCategory"])) {
    $_SESSION["selectedExistingCategory"] = null;
}

if (!isset($_SESSION["selectedExistingSubject"])) {
    $_SESSION["selectedExistingSubject"] = null;
}

if (isset($_POST["selectCategory"])) {
    $_SESSION["selectedCategory"] = $_POST["selectCategory"];

    $getSubjectsWithCategories = "select * from categories_and_subjects where categories_Category = '" . $_SESSION["selectedCategory"] . "'";
    $setSubjectsAndCategories = mysqli_query($conn, $getSubjectsWithCategories);

    while ($row = mysqli_fetch_assoc($setSubjectsAndCategories)) {
        $subjectsCategoriesArray[] = $row;
    }
}

if (isset($_POST["submitCategory"])) {
    addCategoryIntoDB($conn);
    header("Refresh:0.5");
}

if (isset($_POST["submitSubject"])) {
    addSubjectIntoDB($conn);
    header("Refresh:0.5");
}

if (isset($_POST["submitTicket"])) {
    addTicketIntoDB($conn);
}

if (isset($_SESSION["selectedExistingCategory"])) {
    if (isset($_POST["deleteCategory"])) {
        $deleteCategorySQL = "DELETE FROM `subjects` WHERE `subjects`.`Subject` = '" . $_SESSION["selectedExistingSubject"] . "'";

        mysqli_query($conn, $deleteCategorySQL);
    }
}

if (isset($_SESSION["selectedExistingSubject"])) {
    echo isset($_SESSION["selectedExistingSubject"]);
    if (isset($_POST["deleteSubject"])) {
        echo "sub pressed";
        deleteSubject($_SESSION["selectedExistingSubject"], $conn);
        header("Refresh:0.5");
    }
}

if (isset($_SESSION["selectedExistingCategory"])) {
    echo isset($_SESSION["selectedExistingCategory"]);
    if (isset($_POST["deleteCategory"])) {
        echo "cat pressed";
        deleteCategorie($_SESSION["selectedExistingCategory"], $conn);
        header("Refresh:0.5");
    }
}

function addCategoryIntoDB($conn)
{
    $addCategory = $_POST["category"];
    $insertCategory = "insert into categories (Category) values ('$addCategory')";
    if ($addCategory != null) {
        mysqli_query($conn, $insertCategory);
    }
}

function addSubjectIntoDB($conn)
{
    $addSubject = $_POST["subject"];
    $addCategoryForSubject = $_POST["selectCategoryForSubject"];
    $insertSubjectSQL = "insert into subjects (Subject) values ('$addSubject')";
    $insertSubjectAndCategorySQL = "insert into categories_and_subjects (subjects_Subject, categories_Category) VALUES ('$addSubject', '$addCategoryForSubject')";
    if ($addSubject != null && $addCategoryForSubject != null) {
        mysqli_query($conn, $insertSubjectSQL);
        mysqli_query($conn, $insertSubjectAndCategorySQL);
    }
}

function addTicketIntoDB($conn)
{
    $getCategory = $_POST["selectCategory"];
    $getSubject = $_POST["selectSubject"];
    $getLayer = $_POST["layerChooser"];
    $getLanguage = $_POST["language"];
    $getDescription = $_POST["description"];
    $getDeadline = $_POST["deadline"];
    $getTime = $_POST["time"];

    $insertTicketSQL = "insert into tickets (Description, Layer, `Forcast Time`, Deadline, categories_and_subjects_subjects_Subject, Language) 
                        values ('$getDescription', '$getLayer', '$getTime', '$getDeadline', '$getSubject', '$getLanguage')";

    if ($getCategory != null && $getSubject != null && $getLayer != null && $getLanguage != null && $getDescription != null && $getDeadline != null && $getTime != null) {
        mysqli_query($conn, $insertTicketSQL);
    } else {
        echo false;
    }
}

function showExistingCategory($categoriesArray)
{
    if (sizeof($categoriesArray) > 0) {
        foreach ($categoriesArray as $category) {
            echo "<button id='existingButton' name='" . $category["Category"] . "' value='" . $category["Category"] . "' onclick='this.form.submit()'>" . $category["Category"] . "</button>";
        }
    } else echo "<div>There are no Categories yet</div>";
}

function showExistingSubjects($subjectArray, $selectedCategory)
{
    if (isset($selectedCategory)) {
        if (sizeof($subjectArray) > 0) {
            foreach ($subjectArray as $subject) {
                echo "<button id='existingButton' name='" . $subject["subjects_Subject"] . "' value='" . $subject["subjects_Subject"] . "' onclick='this.form.submit()'>" . $subject["subjects_Subject"] . "</button>";
            }
        } else echo "<div>There are no subjects for this category yet</div>";
    } elseif (sizeof($subjectArray) > 0) {
        foreach ($subjectArray as $subject) {

            echo "<button id='existingButton' name='" . $subject["subjects_Subject"] . "' value='" . $subject["subjects_Subject"] . "' onclick='this.form.submit()'>" . $subject["subjects_Subject"] . "</button>";

        }
    } else echo "<div>There are no Subjects yet</div>";
}

function showCategoriesInOptionForTickets($categoriesArray)
{
    if (sizeof($categoriesArray) > 0) {
        foreach ($categoriesArray as $category) {
            if ($category["Category"] === $_SESSION["selectedCategory"]) {
                echo '<option value="' . $category["Category"] . '" selected>' . $category["Category"] . '</option>';
            } else {
                echo '<option value="' . $category["Category"] . '">' . $category["Category"] . '</option>';
            }
        }
    } else echo "<option>There are no categories yet</option>";
}

function showCategoriesInOption($categoriesArray)
{
    if (sizeof($categoriesArray) > 0) {
        foreach ($categoriesArray as $category) {
            echo '<option value="' . $category["Category"] . '">' . $category["Category"] . '</option>';
        }
    } else echo "<option disabled hidden selected>There are no categories yet</option>";
}

function showSubjectsInOption($setSubjectAndCategories)
{
    if (sizeof($setSubjectAndCategories) > 0) {
        foreach ($setSubjectAndCategories as $subject) {
            echo '<option value="' . $subject["subjects_Subject"] . '">' . $subject["subjects_Subject"] . '</option>';
        }
    } elseif ($_SESSION["selectedCategory"] === null) {
        echo "<option disabled hidden selected>First choose a category</option>";
    } else echo "<option disabled hidden selected>There are no subjects for this category</option>";
}

function deleteSubject($selectedSubject, $conn)
{
    $deleteSubjectFromCategoriesAndSubjects = "DELETE FROM `categories_and_subjects` WHERE `categories_and_subjects` . `subjects_Subject` =  '" . $selectedSubject . "'";
    $deleteSubjectFromSubjects = "DELETE FROM `subjects` WHERE `subjects` . `Subject` = '" . $selectedSubject . "'";
    mysqli_query($conn, $deleteSubjectFromSubjects);
    mysqli_query($conn, $deleteSubjectFromCategoriesAndSubjects);
}

function deleteCategorie($selectedCategory, $conn)
{
    $deleteCategoryFromCategories = "DELETE FROM `categories` WHERE `categories` . `Category` = '" . $selectedCategory . "'";
    $deleteCategoryFromCategoriesAndSubjects = "DELETE FROM `categories_and_subjects` WHERE `categories_and_subjects` . `categories_Category` = '" . $selectedCategory . "'";
    mysqli_query($conn, $deleteCategoryFromCategories);
    mysqli_query($conn, $deleteCategoryFromCategoriesAndSubjects);
}

function showSelectedSubject($selectedSubject)
{
    if (isset($selectedSubject)) {
        echo $selectedSubject;
    } else {
        echo "nothing";
    }
}

function showSelectedCategory($selectedCategory)
{
    if (isset($selectedCategory)) {
        echo $selectedCategory;
    } else {
        echo "nothing";
    }
}

?>
<div id="grid">
    <!-- here you can make a category-->
    <div>
        <form class="form" id="formCategory" method="post">
            <h1 class="formTitle">Category</h1>
            <p class="inputTitle">Category</p>
            <input type="text" name="category" class="input" placeholder="ex. PHP">
            <button name="submitCategory" class="Submit">Submit</button>
        </form>
    </div>
    <!--End of category-->

    <!--here you can make the ticket-->
    <form class="form" id="formTicket" method="post">
        <h1 class="formTitle">Ticket</h1>
        <p class="inputTitle">Category</p>
        <select name="selectCategory" id="categorySelection" onchange="this.form.submit()" required>
            <option value="" disabled selected hidden>Choose the Category</option>
            <?php
            showCategoriesInOptionForTickets($categoriesArray);
            ?>
        </select>
        <p class="inputTitle">Subject</p>
        <select name="selectSubject" required>
            <option value="" disabled selected hidden>Choose the subject</option>
            <?php
            showSubjectsInOption($subjectsCategoriesArray);
            ?>
        </select>
        <p class="inputTitle">Layer</p>
        <select name="layerChooser" required>
            <option value="" disabled selected hidden>Choose the layer</option>
            <option value="front-end">Front-end</option>
            <option value="back-end">Back-end</option>
            <option value="front-end_back-end">Front-end and Back-end</option>
        </select>
        <p class="inputTitle">Language</p>
        <input type="text" name="language" placeholder="Language" required>
        <p class="inputTitle">Description</p>
        <input type="text" name="description" placeholder="Description" required>
        <p class="inputTitle">Deadline</p>
        <input type="date" name="deadline" required>
        <p class="inputTitle">Forecast time</p>
        <input type="number" name="time" placeholder="How long will it take" required>
        <button class="Submit" name="submitTicket" onclick="myFunction()">Add</button>
    </form>
    <!-- end of where you can make the ticket-->

    <!-- here you can make a subject-->
    <div>
        <form class="form" id="formSubject" method="post">
            <h1 class="formTitle">Subject</h1>
            <p class="inputTitle">Category</p>
            <select name="selectCategoryForSubject">
                <option value="" disabled selected hidden>Choose a Category</option>
                <?php
                showCategoriesInOption($categoriesArray);
                ?>
            </select>
            <p class="inputTitle">Subject</p>
            <input type="text" name="subject" class="input" placeholder="ex. cookies">
            <button name="submitSubject" class="Submit">Submit</button>
        </form>
    </div>
    <!--end of where you can make a subject-->

    <!-- Start list of existing category's and subjects  -->
    <div class="listGrid">
        <div class="container" id="existingCategories">
            <p id="existingtitle">Category's</p>
            <form class="existingForm" method="post">
                <div class="existingList">
                    <?php
                    showExistingCategory($categoriesArray);
                    ?>
                </div>
                <button class="delete" name="deleteCategory">Delete Category</button>
            </form>
        </div>

        <div class="container">
            <div class="listContainer" id="selectedCategory">
                <p id="existingtitle" class="selectedTitle">Selected Category</p>
                <div class="selectedList">
                    <?php
                    showSelectedCategory($_SESSION["selectedExistingCategory"]);
                    ?>
                </div>
            </div>
            <div class="listContainer" id="selectedSubject">
                <p id="existingtitle" class="selectedTitle">Selected Subject</p>
                <div class="selectedList">
                    <?php
                    showSelectedSubject($_SESSION["selectedExistingSubject"]);
                    ?>
                </div>
            </div>
        </div>

        <div class="container" id="existingSubject">
            <p id="existingtitle">Subject's</p>
            <form class="existingForm" method="post">
                <div class="existingList">
                    <?php
                    showExistingSubjects($subjectsArray, $_SESSION["selectedExistingCategory"]);
                    ?>
                </div>
                <button class="delete" name="deleteSubject">Delete Subject</button>
            </form>
        </div>
    </div>
    <!-- End list of existing category's and subjects  -->
</div>
</body>

<!--jscript for popup message-->
<script>
    function myFunction() {
        var userPreference;

        if (confirm("Do you want to save changes?") === true) {
            userPreference = "Data saved successfully!";
        } else {
            userPreference = "Save Canceled!";
        }

        document.getElementById("msg").innerHTML = userPreference;
    }
</script>
</html>