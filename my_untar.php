#!/usr/bin/php
<?php
unset($argv[0]);
$archiveNames = $argv;

    if (empty($archiveNames))
    {
        echo "Veuillez indiquer au moins le nom d'une archive à décompresser !\n";
        exit;
    }
    
    foreach ($archiveNames as $archive)
    {

        if (!file_exists($archive))
        {
            echo "Erreur : Archive inexistante !\n";
            exit;
        }
    }

    if (unarchive($archiveNames))
    {
        echo "Désarchivage terminé !\n";
    }
    else
    {
        echo "Une erreur est survenue lors du désarchivage !\n";
    }

function unarchive($archiveNames)
{
    $error = false;

    foreach ($archiveNames as $archive)
    {
        $data = file_get_contents($archive);
        $data = gzuncompress($data);
        $jsonArray = json_decode($data, true);

        foreach ($jsonArray as $file)
        {
            if (!is_dir($file['path']))
            {
                mkdir($file['path'], 0755, true);
            }
            if (!file_exists($file['path'] . '/' . $file['name']))
            {

                if (file_put_contents($file['path'] . '/' . $file['name'], utf8_decode($file['content'])) === false)
                {
                    echo 'Erreur lors de l\'écriture du fichier : ' . $file['name'] . "\n";
                }
                else
                {
                    echo 'Le fichier a été extrait : ' . $file['name'] . "\n";
                }
            }
            else
            {
                echo 'Le fichier existe déjà : ' . $file['name'] . "\n";
                $error = true;
            }
        }
    }

    if($error)
    {
        return false;
    }
    else
    {
        return true;
    }
}
?>