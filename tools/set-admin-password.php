<?php
declare(strict_types=1);

/* Sets the admin password on the server.
 *
 *   php tools/set-admin-password.php              generate a random password
 *   php tools/set-admin-password.php 'new-pass'   use a given password
 *
 * The hash goes to private/admin.json. A generated password is written once to
 * private/admin-password.txt (0600) and never printed. */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only');
}

require dirname(__DIR__) . '/inc/bootstrap.php';
require EBT_ROOT . '/inc/auth.php';

$password = $argv[1] ?? '';
$generated = false;

if ($password === '') {
    $password = bin2hex(random_bytes(9));
    $generated = true;
}

if (strlen($password) < 10) {
    fwrite(STDERR, "Password must be at least 10 characters.\n");
    exit(1);
}

if (!ebt_admin_set_password($password)) {
    fwrite(STDERR, 'Could not write ' . ebt_admin_file() . "\n");
    exit(1);
}

echo "Password hash written to " . ebt_admin_file() . "\n";

if ($generated) {
    $file = EBT_PRIVATE . '/admin-password.txt';
    if (file_put_contents($file, $password . "\n") === false) {
        fwrite(STDERR, "Could not write $file\n");
        exit(1);
    }
    chmod($file, 0600);
    echo "Generated password saved to $file\n";
}
