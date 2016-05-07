#!/usr/bin/php
<?php

function get_stdin()
{
    $tmp = fopen('php://stdout', 'w');
    $str = trim(fgets($tmp));
    fclose($tmp);
    return $str;
}

function send_mail($options, $body)
{
    $uid = md5(uniqid(time()));

    $header = "From: Rodolphe Laidet <rodolphe.laidet@epitech.eu>\r\n";
    $header .= "Reply-To: \"rodolphe.laidet@epitech.eu\"\r\n";

    if ($options['f'])
    {
        $file = $options['f'];
        $file_size = filesize($file);
        $handle = fopen($file, "r");
        $content = fread($handle, $file_size);
        fclose($handle);
        $content = chunk_split(base64_encode($content));

        $header .= "MIME-Version: 1.0\r\n";
        $header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
        $header .= "This is a multi-part message in MIME format.\r\n";
        $header .= "--".$uid."\r\n";
        $header .= "Content-type:text/plain; charset=iso-8859-1\r\n";
        $header .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $header .= $body."\r\n\r\n";
        $header .= "--".$uid."\r\n";
        $header .= "Content-Type: application/octet-stream; name=\"".$file."\"\r\n";
        $header .= "Content-Transfer-Encoding: base64\r\n";
        $header .= "Content-Disposition: attachment; filename=\"".$file."\"\r\n\r\n";
        $header .= $content."\r\n\r\n";
        $header .= "--".$uid."--";
    }

    if(mail($options['d'], $options['s'], $body, $header))
    {
        echo "Votre message a bien été envoyé !\n";
        return true;
    }
    else
    {
        echo "Something went wrong...\n";
        return false;
    }
}

function check_options($options)
{
    do
    {
        if (!isset($options['s']))
        {
            while(!isset($options['s']) || empty($options['s']) || $fail_sujet)
            {
                echo "Veuillez entrez le sujet de votre email : ";
                $options['s'] = get_stdin();

                check_abort($options['s']);

                if (empty($options['s']))
                {
                    echo "Votre sujet est vide !\n";
                    $fail_sujet = true;
                }
                else
                {
                    $fail_sujet = false;
                }
            }
        }

        if (!isset($options['d']))
        {
            while(!isset($options['d']) || empty($options['d']))
            {
                echo "Veuillez entrer l'adresse du destinataire : ";
                $options['d'] = get_stdin();
                check_abort($options['d']);

                $options['d'] = check_mail($options['d']);
            }
        }
        else
        {
            $options['d'] = check_mail($options['d']);
            if ($options['d'] == false)
            {
                unset($options['d']);
            }
        }
    } while (!isset($options['d']) || !isset($options['s']));
    return $options;
}

function check_mail($mail)
{
    if (!preg_match('/^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$/', $mail))
    {
        echo "Veuillez entrer une adresse mail valide.\n";
        return false;
    }
    else
    {
        return $mail;
    }
}

function check_file($options)
{
    $choice_fail = true;
    $file_fail = true;
    do
    {
        if (!isset($options['f']))
         {
            while ($choice_fail)
            {
                echo "Voulez vous envoyer un fichier ? (O)ui / (N)on : ";
                $choice = get_stdin();
                check_abort($choice);

                switch($choice)
                {
                    case "O":
                    case "Oui":
                        $choice_fail = false;
                        break;
                    case "N":
                    case "Non":
                        return false;
                        break;
                }
            }
            while ($file_fail)
            {
                echo "Veuillez entrer le nom du fichier à envoyer : ";
                $options['f'] = get_stdin();
                check_abort($options['f']);

                if (!file_exists($options['f']))
                {
                    echo "Il n'existe pas !\n";
                    $file_fail = true;
                }
                else
                {
                    return $options['f'];
                }
            }
        }
        else
        {
            if (!file_exists($options['f']))
            {
                echo "Votre fichier n'existe pas !\n";
                exit;
            }
            else
            {
                return $options['f'];
            }
        }
    } while (!isset($options['f']));
}

function check_abort($str)
{
    switch($str)
    {
        case "abort":
        case "quit":
        case "exit":
        case "q":
            echo "Bye bye !\n";
            exit;
            break;
    }
}

function check_body()
{
    $body = NULL;
    echo "Veuillez entrer votre message : \n";
    while ($input = fgets(STDIN))
    {
        if (preg_match('/^\.$/', $input))
        {
            break;
        }

        $body .= $input;
    }
    return $body;
}

$options = getopt("d:s:f:");
$options = check_options($options);
$options['f'] = check_file($options);
$body = check_body();
send_mail($options, $body);
?>