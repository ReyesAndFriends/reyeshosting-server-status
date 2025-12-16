<?php
/* ================= CONFIG ================= */

$server_uuid = "";
$api_key     = "";
$panel_url   = "";

/* ========================================== */

function apiRequest($url, $api_key) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer $api_key",
            "Accept: application/vnd.pterodactyl.v1+json"
        ],
        CURLOPT_TIMEOUT => 5
    ]);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($code === 200) ? json_decode($res, true) : null;
}

function mb($bytes) {
    return number_format($bytes / 1024 / 1024, 0) . " MB";
}


$info = apiRequest("$panel_url/api/client/servers/$server_uuid", $api_key);
$res  = apiRequest("$panel_url/api/client/servers/$server_uuid/resources", $api_key);

$server = $info['attributes'] ?? null;
$stats  = $res['attributes'] ?? null;


$name        = $server['name'] ?? "Servidor";
$description = $server['description'] ?? "Sin descripción";

$state = $stats['current_state'] ?? "offline";
$online = $state === "running";

$cpu  = isset($stats['resources']) ? round($stats['resources']['cpu_absolute'], 1) . "%" : "-";
$ram  = isset($stats['resources']) ? mb($stats['resources']['memory_bytes']) : "-";
$disk = isset($stats['resources']) ? mb($stats['resources']['disk_bytes']) : "-";

$state_color = match ($state) {
    "running" => "text-green-600",
    "starting", "stopping" => "text-yellow-500",
    default => "text-red-500"
};

$state_label = ucfirst($state);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($name) ?> · Estado</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-black text-zinc-900 min-h-screen flex items-center justify-center">

<img
  src="https://cdn.reyesandfriends.cl/assets/minecraft-bg.jpg"
  class="fixed inset-0 w-full h-full object-cover blur z-0 pointer-events-none"
/>

<div class="bg-white/95 backdrop-blur rounded-2xl shadow-xl p-8 max-w-[95%] md:max-w-3xl w-full z-10">

  <h1 class="font-bold mb-1 text-center md:text-3xl">
    <?= htmlspecialchars($name) ?>
  </h1>

  <p class="text-zinc-500 text-center mb-6">
    <?= htmlspecialchars($description) ?>
  </p>

  <div class="flex justify-center mb-6">
    <span class="inline-flex items-center gap-2 font-semibold <?= $state_color ?>">
      <span class="w-3 h-3 rounded-full bg-current"></span>
      <?= $state_label ?>
    </span>
  </div>

  <div class="grid grid-cols-3 gap-4 text-sm mb-6">

    <div class="bg-zinc-50 rounded-lg p-3 border text-center">
      <div class="text-zinc-500">CPU</div>
      <div class="font-bold"><?= $cpu ?></div>
    </div>

    <div class="bg-zinc-50 rounded-lg p-3 border text-center">
      <div class="text-zinc-500">RAM</div>
      <div class="font-bold"><?= $ram ?></div>
    </div>

    <div class="bg-zinc-50 rounded-lg p-3 border text-center">
      <div class="text-zinc-500">Disco</div>
      <div class="font-bold"><?= $disk ?></div>
    </div>

  </div>

  <div class="bg-zinc-100 rounded-lg p-4 border text-center">
    <div class="text-zinc-500 text-sm mb-1">Servidor</div>
    <code class="font-mono text-indigo-600 select-all">
      <?= htmlspecialchars($name) ?>
    </code>
  </div>

</div>

</body>
</html>
