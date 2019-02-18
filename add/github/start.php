<?php
$errors = [];
if (!empty($_POST)) {
    if (!isset($_POST['repoOwner']) || empty($_POST['repoOwner'])) $errors[] = "Repository Owner is required";
    if (!isset($_POST['repoName']) || empty($_POST['repoName'])) $errors[] = "Repository Name is required";
    if (!isset($_POST['repoRef']) || empty($_POST['repoRef'])) $errors[] = "Repository Ref/Branch is required";
    if (empty($errors)) {
        redirect("/add/github/" . $_POST['repoOwner'] . "/" . $_POST['repoName'] . "/" . $_POST['repoRef']);
    }
}
?>
<html lang="en">
<body>
<form action="<?php echo $fullRoute; ?>" method="post">
    <input type="text" placeholder="Repo Owner" value="<?php echo $_POST['repoOwner'] ?? ''; ?>" id="repoOwner"
           name="repoOwner" required/>
    <input type="text" placeholder="Repo Name" value="<?php echo $_POST['repoName'] ?? ''; ?>" id="repoName"
           name="repoName" required/>
    <input type="text" placeholder="Repo Ref" value="<?php echo $_POST['repoRef'] ?? 'master'; ?>" id="repoRef"
           name="repoRef" required/>
    <input type="submit" value="Add to cdnjs"/>
</form>
</body>
</html>