#!/usr/bin/php
<?php
    $tree = array();
    $archiveName = "output.mytar";
    $jsonArray = array();
    $filesArray = $argv;
    array_splice($filesArray, 0, 1);

    if (empty($filesArray))
    {
        echo "Veuillez entrer le nom des fichiers à archiver.\n";
        exit;
    }
    elseif (count($filesArray) < 2)
    {
        echo "Veuillez choisir au moins 2 fichiers à archiver.\n";
        exit;
    }

    $tree = list_files_folders($filesArray);
    $jsonArray = tree_to_json($tree, $jsonArray);
    $archiveName = addFilesToArchive($archiveName, $jsonArray);
    compress($archiveName);


function list_files_folders($filesArray)
{
    global $tree;
    foreach ($filesArray as $file)
    {
        if (is_dir($file))
        {
            $tree = scan_folder('/' .   $file);
        }
        elseif (file_exists($file))
        {
            array_push($tree, "./" . $file);
        }
        else
        {
            echo "Erreur : $file n'existe pas !\n";
            exit;
        }
    }
    return $tree;
}

function scan_folder($directory)
{
    global $tree;
    $relative = '.'.$directory;
    if($dh = opendir($relative))
    {
        while(false !== ($file = readdir($dh)))
        {
            if(($file !== '.') && ($file !== '..'))
            {
                if(!is_dir($relative . $file))
                {
                    array_push($tree, "." . $directory . $file);
                }
                else
                {
                    scan_folder($directory.$file.'/');
                }
            }
        }
    }
    return $tree;
}

function createArchive($name)
{
        fopen($name, 'a');
        echo "Création de l'archive : " . $name . "\n";
        return true;
}

function tree_to_json($tree, $jsonArray)
{
    foreach ($tree as $file)
    {
        $tmp = explode('/', $file);
        $filename = end($tmp);
        $fileContent = file_get_contents($file);
        $path = array_slice($tmp, 0, -1);
        $path = implode("/", $path);

        $jsonArray[] = array('name' => $filename, 'path' => $path, 'content' => $fileContent);
    }
    return $jsonArray;
}

function addFilesToArchive($archiveName, $jsonArray)
{
    createArchive($archiveName);

    foreach ($jsonArray as $file)
    {
            echo "Ajout du fichier : " . $file['name'] . "\n";
            file_put_contents($archiveName, utf8_encode(json_encode($jsonArray)));
    }
    echo "Archivage terminé !\n";
    return $archiveName;
}

function compress($archiveName)
{
    $tmp = file_get_contents($archiveName);
    $tmp = gzcompress($tmp);
    if(file_put_contents($archiveName, $tmp))
    {
        echo "Compression terminée ! \n";
        return true;
    }
    else
    {
        echo "Une erreur s'est produite... dommage !\n";
        return false;
    }
}
?>