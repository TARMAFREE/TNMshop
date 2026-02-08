<template>
    <div class="home">
      <div class="card home-card">
        <div class="card-body">
          <div class="badge">TNMshop</div>
          <h1 class="title">Welcome</h1>
          <p class="muted">กรุณาเลือกประเภทผู้ใช้งาน</p>
  
          <!-- ปุ่มหลัก 2 อันซ้าย/ขวา -->
          <div class="pick">
            <RouterLink to="/login" class="btn primary big">Customer</RouterLink>
            <RouterLink to="/admin" class="btn danger big">Admin</RouterLink>
          </div>
  
          <div class="hr"></div>
  
          <!-- Guest: เข้าร้านได้เลย ไม่ต้องล็อกอิน -->
          <RouterLink to="/shop" class="btn guest-btn">
            Continue as Guest
          </RouterLink>
  
          <div class="muted" style="margin-top:10px; font-size:12px;">
            Guest สามารถดูสินค้า ใส่ตะกร้า Checkout และชำระเงินได้ โดยไม่ต้องสมัครสมาชิก
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <style scoped>
  .home{
    min-height: calc(100vh - 140px);
    display:flex;
    align-items:center;
    justify-content:center;
  }
  .home-card{
    width: min(900px, 92vw);
    background: linear-gradient(180deg, rgba(255,255,255,0.03), rgba(255,255,255,0.01));
  }
  .title{
    margin: 10px 0 6px;
    font-size: 40px;
  }
  .pick{
    display:flex;
    gap: 14px;
    margin-top: 18px;
  }
  .big{
    flex: 1;
    height: 90px;
    border-radius: 16px;
    font-size: 20px;
    font-weight: 800;
  }
  .guest-btn{
    width: 100%;
    height: 54px;
    border-radius: 14px;
    font-weight: 800;
    border: 1px solid var(--line);
    background: transparent;
  }
  .guest-btn:hover{
    opacity: .95;
  }
  
  @media (max-width: 620px){
    .pick{ flex-direction: column; }
    .big{ height: 76px; font-size: 18px; }
    .title{ font-size: 34px; }
  }
  </style>
  