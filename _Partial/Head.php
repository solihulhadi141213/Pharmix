<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<title><?php echo "$title_page"; ?></title>
<meta content="<?php echo "$deskripsi"; ?>" name="description">
<meta content="<?php echo "$kata_kunci"; ?>" name="keywords">

<!-- Favicons -->
<link href="assets/img/<?php echo "$favicon"; ?>" rel="icon">
<link href="assets/img/<?php echo "$favicon"; ?>" rel="apple-touch-icon">

<!-- Google Fonts -->
<link href="assets/fonts/fonts.css" rel="stylesheet">

<!-- Vendor CSS Files -->
<link href="node_modules/bootstrap/dist/css/bootstrap.min.css?v=<?php echo date('YmdHis'); ?>" rel="stylesheet">
<link href="node_modules/bootstrap-icons/font/bootstrap-icons.css?v=<?php echo date('YmdHis'); ?>" rel="stylesheet">
<link href="node_modules/boxicons/css/boxicons.min.css?v=<?php echo date('YmdHis'); ?>" rel="stylesheet">
<link href="node_modules/quill/dist/quill.snow.css" rel="stylesheet">
<link href="node_modules/quill/dist/quill.bubble.css" rel="stylesheet">
<link href="node_modules/remixicon/fonts/remixicon.css" rel="stylesheet">
<link href="node_modules/mdb-ui-kit/css/mdb.min.css" rel="stylesheet">

<!-- Custome CSS -->
<link href="assets/css/style.css?v=<?php echo date('YmdHis'); ?>" rel="stylesheet">
<script>
    if (localStorage.getItem('theme_mode') === 'dark') {
        document.documentElement.classList.add('dark-mode');
    }
</script>

<!-- Header JS -->
<script type="text/javascript" src="node_modules/jquery/dist/jquery.min.js"></script>
<script type="text/javascript" src="node_modules/marked/marked.min.js"></script>

