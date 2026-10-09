<template>
    <section class="panel">
        <span class="eyebrow">KampusLMS</span><h1>404</h1><h2>Halaman tidak ditemukan</h2>
        <p>Halaman yang kamu cari tidak ditemukan atau sudah dipindahkan.</p>
        <RouterLink class="button" to="/dashboard">Kembali ke dashboard</RouterLink>
    </section>
</template>

<style scoped>
.panel { max-width:680px; margin:8vh auto; padding:clamp(28px,7vw,64px); border:1px solid #e7e4ee; border-radius:16px; background:#fff; text-align:center; }
.eyebrow { color:#7163bb; font-weight:800; text-transform:uppercase; }.panel h1 { color:#6552bd; font-size:clamp(4rem,12vw,7rem); }
</style>
