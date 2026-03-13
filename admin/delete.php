<?php


include "../config/database.php";

$id = $_GET['id'];

// récupérer les fichiers dans la table fichiers
$sql = "SELECT * FROM fichiers WHERE id_fichier = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id'=>$id]);
$fichier = $stmt->fetch();

if($fichier){

    // supprimer les étudiants liés au fichier
    $sql = "DELETE FROM etudiant WHERE id_fichier = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id'=>$id]);

    //  supprimer le fichier dans le dossier
    $chemin = "../doc/" . $fichier['nom_fichier'];
    if(file_exists($chemin)){
        unlink($chemin);
    }

    //  supprimer le fichier dans la table fichiers
    $sql = "DELETE FROM fichiers WHERE id_fichier = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id'=>$id]);
}

header("Location: ../admin/admin_dash.php");







/*
include "../config/database.php";
$id = $_GET['id'];

$sql = "SELECT * FROM fichiers WHERE id_fichier=:id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id'=>$id]);
$fichier = $stmt->fetch();

// 1 supprimer les étudiants liés au fichier
$sql = "DELETE FROM etudiant WHERE id_fichier = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id'=>$id]);


// 2 supprimer le fichier
$sql = "DELETE FROM fichiers WHERE id_fichier = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id'=>$id]);

// supprimer le fichier dans le dossier
unlink("../doc/" . $fichier['nom_fichier']);




header("Location: ../admin/admin_dash.php");*/

?>