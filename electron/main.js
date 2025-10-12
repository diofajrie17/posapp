const { app, BrowserWindow } = require("electron");
const path = require("path");
const { spawn } = require("child_process");
const kill = require("tree-kill");
const waitOn = require("wait-on");
const fs = require("fs");

let win = null;
let phpServer = null;

const PORT = process.env.APP_PORT || 9000;
const HOST = "127.0.0.1";
const APP_URL = `http://${HOST}:${PORT}`;

/**
 * Cari path PHP
 */
function getPhpPath() {
  if (process.env.APP_PHP_PATH && fs.existsSync(process.env.APP_PHP_PATH)) {
    return process.env.APP_PHP_PATH;
  }
  return "php"; // pastikan php ada di PATH (xampp/php sudah didaftarkan)
}

const PHP_PATH = getPhpPath();
console.log("✅ Using PHP path:", PHP_PATH);

/**
 * Helper jalankan command child process
 */
function run(cmd, args, cwd) {
  const p = spawn(cmd, args, { cwd, env: process.env, shell: true });
  p.stdout.on("data", (d) => console.log(String(d)));
  p.stderr.on("data", (d) => console.error(String(d)));
  p.on("error", (err) => console.error("❌ Spawn error:", err));
  return p;
}

/**
 * Start Laravel
 */
async function startLaravel() {
  const cwd = app.isPackaged
    ? path.join(process.resourcesPath, "laravel")
    : path.join(__dirname, ".."); // root Laravel saat dev

  console.log("📂 Laravel cwd:", cwd);

  phpServer = run(
    PHP_PATH,
    ["artisan", "serve", `--host=${HOST}`, `--port=${PORT}`],
    cwd
  );

  // tunggu Laravel server ready
  await waitOn({ resources: [APP_URL], timeout: 30000 });
}

/**
 * Buat window Electron
 */
function createWindow() {
  win = new BrowserWindow({
    width: 1280,
    height: 800,
    autoHideMenuBar: true,
    webPreferences: {
      nodeIntegration: false,
    },
  });

  win.loadURL(APP_URL);

  win.on("closed", () => {
    win = null;
  });
}

/**
 * Lifecycle Electron
 */
app.on("ready", async () => {
  try {
    await startLaravel();
    createWindow();
  } catch (err) {
    console.error("❌ Failed to start Laravel server:", err);
    app.quit();
  }
});

app.on("before-quit", () => {
  if (phpServer && phpServer.pid) kill(phpServer.pid);
});
