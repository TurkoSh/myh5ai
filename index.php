<?php
function is_image($filename)
{
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'bmp']);
}

function createTree($dir, $level = 0)
{
    $html = '';

    if ($level === 0) {
        $html .= "<ul>\n";
    }

    $files = scandir($dir);
    sort($files);

    foreach ($files as $file) {
        if ($file == '.' || $file == '..') continue;

        $filePath = $dir . '/' . $file;
        $isDir = is_dir($filePath);

        $relativePath = substr($filePath, strlen(realpath(__DIR__)));

        $html .= '<li>';
        if ($isDir) {
            $html .= '<a href="?path=' . urlencode($relativePath) . '"><i class="fas fa-folder"></i> ' . $file . '</a>';
            if ($relativePath === $GLOBALS['path']) {
                $html .= createTree($filePath, $level);
            }
        }
        $html .= "</li>\n";
    }

    if ($level === 0) {
        $html .= "</ul>\n";
    }

    return $html;
}


function goBack()
{
    $path = isset($_GET['path']) ? $_GET['path'] : '';
    $dir = dirname($path);
    return $dir === '.' ? '' : $dir;
}

function goForward()
{
    $path = isset($_GET['path']) ? $_GET['path'] : '';
    return $path;
}



$realBase = realpath(__DIR__);

$path = isset($_GET['path']) ? $_GET['path'] : '';
$realUserPath = realpath($realBase . '/' . $path);

if ($realUserPath === false || strpos($realUserPath, $realBase) !== 0) {
    die('Accès non autorisé');
}

$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name';

$search = isset($_GET['search']) ? $_GET['search'] : '';

$view = isset($_GET['view']) ? $_GET['view'] : '';

if ($view !== '') {
    $viewPath = $realBase . '/' . $view;
    $realViewPath = realpath($viewPath);

    if ($realViewPath === false || strpos($realViewPath, $realBase) !== 0) {
        die('Accès non autorisé');
    }

    if (is_file($realViewPath)) {
        header('Content-Type: text/plain');
        readfile($realViewPath);
        exit;
    }
}

$iconColor = isset($_GET['icon_color']) ? $_GET['icon_color'] : '#ffffff';


$files = array_filter(scandir($realUserPath), function ($file) use ($search) {
    return $search === '' || strpos($file, $search) !== false;
});

usort($files, function ($a, $b) use ($sort, $realUserPath) {
    if ($sort === 'size') {
        return filesize($realUserPath . '/' . $a) <=> filesize($realUserPath . '/' . $b);
    } elseif ($sort === 'date') {
        return filemtime($realUserPath . '/' . $a) <=> filemtime($realUserPath . '/' . $b);
    } else {
        return strcmp($a, $b);
    }
});
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" />
    <style>
        .data-cell.name .fas {
            color: <?php echo $iconColor; ?>;
        }
    </style>

    <title>H5AI</title>
</head>

<body>

    <header>
        <nav class="navbar">
            <div class="nav-left">
                <div class="nav-item">
                    <a href="#" id="sidebar-toggle"><i class="fas fa-bars"></i></a>
                </div>
                <form action="index.php" method="get" class="nav-item">
                    <input type="hidden" name="path" value="<?php echo $path; ?>">
                    <input type="text" name="search" placeholder="Rechercher" value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                    <input type="color" name="icon_color" title="Choisir la couleur des icônes" value="<?php echo isset($_GET['icon_color']) ? $_GET['icon_color'] : '#ffffff'; ?>">
                </form>
                <div class="nav-item">
                    <span class="nav-text">H5AI</span>
                </div>
                <div class="nav-item">
                    <a href="#"><i class="fas fa-folder"></i></a>
                </div>
                <div class="nav-item">
                    <a href="#"><i class="fas fa-chevron-right"></i></a>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <div class="tree">
            <?php echo createTree($realBase); ?>
        </div>
        <div class="content">
            <div class="header-row">
                <div class="header-cell name"><a href="?sort=name">Nom</a></div>
                <div class="header-cell date"><a href="?sort=date">Date de modification</a></div>
                <div class="header-cell size"><a href="?sort=size">Taille</a></div>
                <div class="navigation-arrows">
                    <a href="?path=<?php echo urlencode(goBack()); ?>" class="arrow-back"><i class="fas fa-arrow-left"></i></a>
                    <a href="?path=<?php echo urlencode(goForward()); ?>" class="arrow-forward"><i class="fas fa-arrow-right"></i></a>
                </div>
            </div>

            <?php
            foreach ($files as $file) {
                if ($file == '.' || $file == '..') continue;

                $filePath = $realUserPath . '/' . $file;
                $isDir = is_dir($filePath);
                $size = $isDir ? '' : filesize($filePath);
                $lastModified = date("Y-m-d H:i:s", filemtime($filePath));
            ?>
                <div class="data-row">
                    <div class="data-cell name">
                        <?php if ($isDir) : ?>
                            <a href="?path=<?php echo urlencode($path . '/' . $file); ?>">
                            <?php elseif (is_image($file)) : ?>
                                <a href="/H5AI/W-PHP-501-NCE-1-1-myh5ai-turko.shakhidov<?php echo htmlspecialchars($path . '/' . $file); ?>" target="_blank">
                                <?php else : ?>
                                    <a href="?view=<?php echo urlencode($path . '/' . $file); ?>">
                                    <?php endif; ?>
                                    <?php echo $isDir ? '<i class="fas fa-folder"></i>' : '<i class="fas fa-file"></i>'; ?> <?php echo $file; ?>
                                    </a>
                    </div>
                    <div class="data-cell date"><?php echo $lastModified; ?></div>
                    <div class="data-cell size"><?php echo $isDir ? '-' : formatSizeUnits($size); ?></div>
                </div>
            <?php
            }
            function formatSizeUnits($bytes)
            {
                if ($bytes >= 1073741824) {
                    $bytes = number_format($bytes / 1073741824, 2) . ' GB';
                } elseif ($bytes >= 1048576) {
                    $bytes = number_format($bytes / 1048576, 2) . ' MB';
                } elseif ($bytes >= 1024) {
                    $bytes = number_format($bytes / 1024, 2) . ' KB';
                } elseif ($bytes > 1) {
                    $bytes = $bytes . ' bytes';
                } elseif ($bytes == 1) {
                    $bytes = $bytes . ' byte';
                } else {
                    $bytes = '0 bytes';
                }

                return $bytes;
            }
            ?>
        </div>
    </main>

</body>

</html>