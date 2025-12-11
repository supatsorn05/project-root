<!-- src/views/NotFound.vue -->
<template>
  <div class="nf">
    <div class="nf__bg" aria-hidden="true"></div>

    <section class="nf__card" role="group" aria-label="ไม่พบหน้า">
      <div class="nf__badge" aria-hidden="true">404</div>

      <h1>ไม่พบหน้า</h1>
      <p class="muted">
        ขออภัย เราไม่พบหน้า
        <code class="path">{{ $route.fullPath }}</code>
        ที่คุณต้องการ
      </p>

      <div class="nf__actions">
        <RouterLink class="btn btn-primary" to="/">กลับหน้าหลัก</RouterLink>
        <button class="btn" type="button" @click="$router.back()">ย้อนกลับ</button>
      </div>
    </section>
  </div>
</template>

<style scoped>
:root{
  --bg:#fff; --fg:#111; --muted:#666;
  --line:#e9e9e9; --shadow:0 20px 50px rgba(0,0,0,.08);
  --radius:20px; --t:.2s cubic-bezier(.2,.7,.3,1);
}

/* เต็มจอ จัดกลาง */
.nf{
  min-height: 80svh;
  display: grid; place-items: center;
  padding: 40px 16px;
  background: var(--bg); color: var(--fg);
  position: relative; overflow: clip;
  font-synthesis-weight: none;
}

/* พื้นหลังบาง ๆ */
.nf__bg{
  position: absolute; inset: 0; pointer-events: none;
  background:
    radial-gradient(800px 400px at 90% -10%, rgba(0,0,0,.04), transparent 60%),
    radial-gradient(700px 350px at -10% 100%, rgba(0,0,0,.05), transparent 60%);
  mix-blend-mode: multiply;
}

/* การ์ด (ไม่มี hover) */
.nf__card{
  width: min(680px, 100%);
  background: #fff;
  border: 1px solid var(--line);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  padding: clamp(24px, 4vw, 36px);
  text-align: center;
  position: relative;
  z-index: 1;
}

/* ป้าย 404 */
.nf__badge{
  width: max-content; margin-inline: auto; margin-bottom: 8px;
  padding: 6px 12px; border: 1px solid var(--line);
  border-radius: 999px; font-weight: 900; letter-spacing: .12em;
  color: var(--fg); background: #f9f9f9;
}

h1{ margin: 6px 0 8px; font-size: clamp(22px, 2.2rem, 32px); font-weight: 800; }
.muted{ color: var(--muted); }
.path{
  background: #f5f5f5; border: 1px solid #ececec;
  border-radius: 8px; padding: 2px 8px; color: #222;
}

/* ปุ่ม */
.nf__actions{ margin-top: 18px; display: inline-flex; gap: 10px; flex-wrap: wrap; justify-content: center; }
.btn{
  display:inline-flex; align-items:center; justify-content:center; gap:8px;
  padding:10px 16px; border-radius:12px; cursor:pointer;
  border:1px solid #111; background:#fff; color:#111; font-weight:700;
  transition: background var(--t), color var(--t);
}
.btn:hover{ background:#111; color:#fff; }      /* hover แค่ปุ่ม */
.btn-primary{ background:#111; color:#fff; }
.btn-primary:hover{ background:#000; }

/* a11y */
.btn:focus-visible{ outline:2px solid #111; outline-offset:2px; border-radius:14px; }
</style>
