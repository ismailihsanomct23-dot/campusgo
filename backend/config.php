<?php
return [
  'db_type' => 'sqlite', // 'sqlite' or 'mysql'
  'db_path' => __DIR__ . '/campusgo.db', // For SQLite
  'timezone' => 'Asia/Kolkata',
  
  // Legacy MySQL config (if needed in future)
  'db_host' => '127.0.0.1',
  'db_port' => 3306,
  'db_name' => 'campusgo',
  'db_user' => 'root',
  'db_pass' => '',
];
