<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'cctv_db');
define('DB_USER', 'root');
define('DB_PASS', '');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}

$db = getDB();

// 최근 50건 로그
$logs = $db->query("
    SELECT id, camera_id, location, vehicle_plate, measured_speed, speed_limit, is_violation, recorded_at
    FROM speed_logs
    ORDER BY id DESC
    LIMIT 50
")->fetchAll();

// 통계 (최근 10분)
$stats = $db->query("
    SELECT
        COUNT(*) AS total,
        SUM(is_violation) AS violations,
        ROUND(AVG(measured_speed), 1) AS avg_speed,
        MAX(measured_speed) AS max_speed,
        MIN(measured_speed) AS min_speed
    FROM speed_logs
    WHERE recorded_at >= NOW() - INTERVAL 10 MINUTE
")->fetch();

// 카메라별 최신 감지
$cameras = $db->query("
    SELECT s.camera_id, s.location, s.measured_speed, s.vehicle_plate, s.is_violation, s.recorded_at
    FROM speed_logs s
    INNER JOIN (
        SELECT camera_id, MAX(id) AS max_id FROM speed_logs GROUP BY camera_id
    ) latest ON s.id = latest.max_id
    ORDER BY s.camera_id
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="5">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CCTV 차량 속도 모니터링</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #0d1117; color: #c9d1d9; font-family: 'Segoe UI', sans-serif; }

        .header {
            background: linear-gradient(135deg, #161b22, #21262d);
            border-bottom: 2px solid #30363d;
            padding: 16px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .header h1 { font-size: 1.4rem; color: #58a6ff; }
        .header h1 span { color: #f0f6fc; }
        .live-badge {
            display: flex; align-items: center; gap: 8px;
            background: #161b22; border: 1px solid #30363d;
            padding: 6px 14px; border-radius: 20px; font-size: 0.8rem;
        }
        .live-dot {
            width: 8px; height: 8px; background: #3fb950;
            border-radius: 50%; animation: pulse 1.5s infinite;
        }
        @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.3} }

        .container { padding: 20px 24px; max-width: 1400px; margin: 0 auto; }

        /* 통계 카드 */
        .stats-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; margin-bottom: 20px; }
        .stat-card {
            background: #161b22; border: 1px solid #30363d;
            border-radius: 8px; padding: 16px; text-align: center;
        }
        .stat-card .label { font-size: 0.75rem; color: #8b949e; margin-bottom: 6px; text-transform: uppercase; }
        .stat-card .value { font-size: 1.8rem; font-weight: 700; }
        .stat-card.danger .value  { color: #f85149; }
        .stat-card.warning .value { color: #e3b341; }
        .stat-card.safe .value    { color: #3fb950; }
        .stat-card.info .value    { color: #58a6ff; }
        .stat-card.neutral .value { color: #f0f6fc; }

        /* 카메라 카드 */
        .section-title {
            font-size: 0.85rem; color: #8b949e; text-transform: uppercase;
            letter-spacing: 0.08em; margin-bottom: 12px;
        }
        .cameras-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 12px; margin-bottom: 24px;
        }
        .camera-card {
            background: #161b22; border: 1px solid #30363d;
            border-radius: 8px; padding: 14px 16px; position: relative; overflow: hidden;
        }
        .camera-card.violation { border-color: #f85149; background: #1a0c0c; }
        .camera-card.normal    { border-color: #238636; }
        .camera-id       { font-size: 0.8rem; color: #8b949e; margin-bottom: 4px; }
        .camera-location { font-size: 0.82rem; color: #c9d1d9; margin-bottom: 10px;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .camera-speed    { font-size: 2rem; font-weight: 800; }
        .camera-card.violation .camera-speed { color: #f85149; }
        .camera-card.normal    .camera-speed { color: #3fb950; }
        .camera-unit  { font-size: 0.85rem; color: #8b949e; margin-left: 4px; }
        .camera-plate { font-size: 0.8rem; color: #8b949e; margin-top: 6px; }
        .violation-badge {
            position: absolute; top: 10px; right: 10px;
            background: #f85149; color: white; font-size: 0.7rem;
            padding: 2px 8px; border-radius: 12px; font-weight: 600;
        }

        /* 로그 테이블 */
        .log-section { background: #161b22; border: 1px solid #30363d; border-radius: 8px; overflow: hidden; }
        .log-header {
            padding: 14px 16px; border-bottom: 1px solid #30363d;
            display: flex; justify-content: space-between; align-items: center;
        }
        .log-header h2 { font-size: 0.95rem; color: #f0f6fc; }
        .refresh-info  { font-size: 0.75rem; color: #8b949e; }
        table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
        th {
            background: #21262d; color: #8b949e; font-weight: 600;
            text-transform: uppercase; font-size: 0.75rem; padding: 10px 14px; text-align: left;
        }
        td { padding: 9px 14px; border-bottom: 1px solid #21262d; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #21262d; }
        .speed-cell { font-weight: 700; }
        .speed-over { color: #f85149; }
        .speed-ok   { color: #3fb950; }
        .badge {
            display: inline-block; padding: 2px 8px; border-radius: 12px;
            font-size: 0.72rem; font-weight: 600;
        }
        .badge-violation { background: #3d0c0c; color: #f85149; border: 1px solid #f85149; }
        .badge-normal    { background: #0d1f0f; color: #3fb950; border: 1px solid #3fb950; }
        .plate { font-family: monospace; background: #21262d; padding: 2px 6px; border-radius: 4px; }

        .footer { text-align: center; padding: 16px; color: #484f58; font-size: 0.75rem; }
    </style>
</head>
<body>

<div class="header">
    <h1>🚦 CCTV <span>차량 속도 모니터링</span></h1>
    <div class="live-badge">
        <div class="live-dot"></div>
        LIVE &nbsp;|&nbsp; 5초 자동 갱신 &nbsp;|&nbsp; <?= date('Y-m-d H:i:s') ?>
    </div>
</div>

<div class="container">

    <!-- 통계 -->
    <div class="stats-grid">
        <div class="stat-card neutral">
            <div class="label">최근 10분 감지</div>
            <div class="value"><?= number_format($stats['total'] ?? 0) ?></div>
        </div>
        <div class="stat-card danger">
            <div class="label">과속 건수</div>
            <div class="value"><?= number_format($stats['violations'] ?? 0) ?></div>
        </div>
        <div class="stat-card info">
            <div class="label">평균 속도</div>
            <div class="value"><?= $stats['avg_speed'] ?? '-' ?><span style="font-size:1rem"> km/h</span></div>
        </div>
        <div class="stat-card warning">
            <div class="label">최고 속도</div>
            <div class="value"><?= $stats['max_speed'] ?? '-' ?><span style="font-size:1rem"> km/h</span></div>
        </div>
        <div class="stat-card safe">
            <div class="label">제한 속도</div>
            <div class="value">100<span style="font-size:1rem"> km/h</span></div>
        </div>
    </div>

    <!-- 카메라 현황 -->
    <div class="section-title">카메라 현황 (최신 감지)</div>
    <div class="cameras-grid">
        <?php foreach ($cameras as $cam): ?>
        <?php $vio = (bool)$cam['is_violation']; ?>
        <div class="camera-card <?= $vio ? 'violation' : 'normal' ?>">
            <?php if ($vio): ?><div class="violation-badge">과속</div><?php endif; ?>
            <div class="camera-id"><?= htmlspecialchars($cam['camera_id']) ?></div>
            <div class="camera-location"><?= htmlspecialchars($cam['location']) ?></div>
            <div>
                <span class="camera-speed"><?= number_format($cam['measured_speed'], 1) ?></span>
                <span class="camera-unit">km/h</span>
            </div>
            <div class="camera-plate">번호판: <?= htmlspecialchars($cam['vehicle_plate']) ?></div>
            <div class="camera-plate" style="color:#484f58">
                <?= date('H:i:s', strtotime($cam['recorded_at'])) ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- 로그 테이블 -->
    <div class="log-section">
        <div class="log-header">
            <h2>최근 감지 로그 (최신 50건)</h2>
            <span class="refresh-info">페이지 자동 새로고침: 5초</span>
        </div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>카메라</th>
                    <th>위치</th>
                    <th>번호판</th>
                    <th>측정 속도</th>
                    <th>제한 속도</th>
                    <th>상태</th>
                    <th>감지 시각</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $row): ?>
                <?php $over = (bool)$row['is_violation']; ?>
                <tr>
                    <td style="color:#484f58"><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['camera_id']) ?></td>
                    <td style="color:#8b949e;font-size:0.8rem"><?= htmlspecialchars($row['location']) ?></td>
                    <td><span class="plate"><?= htmlspecialchars($row['vehicle_plate']) ?></span></td>
                    <td class="speed-cell <?= $over ? 'speed-over' : 'speed-ok' ?>">
                        <?= number_format($row['measured_speed'], 1) ?> km/h
                    </td>
                    <td style="color:#8b949e"><?= $row['speed_limit'] ?> km/h</td>
                    <td>
                        <span class="badge <?= $over ? 'badge-violation' : 'badge-normal' ?>">
                            <?= $over ? '과속' : '정상' ?>
                        </span>
                    </td>
                    <td style="color:#8b949e"><?= $row['recorded_at'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<div class="footer">
    CCTV Speed Monitoring System &nbsp;|&nbsp; 도로 제한속도 100 km/h &nbsp;|&nbsp;
    마지막 갱신: <?= date('H:i:s') ?>
</div>

</body>
</html>