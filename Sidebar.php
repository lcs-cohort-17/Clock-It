<button class="sidebar-toggle sidebar-mobile" type="button" aria-label="Toggle sidebar">
<i class="fas fa-bars"></i>
</button>

<aside
class="sidebar"
:class="sidebarOpen ? 'show-sidebar' : ''"
>

<div class="sidebar-header">

<div class="logo-box">
<i class="fas fa-clock"></i>
</div>

<div>
<h3>Clock It</h3>
<small>Staff Portal</small>
</div>

</div>

<nav class="sidebar-nav" aria-label="Main navigation">
<ul class="sidebar-nav-list">
<li>
<a href="#dashboard" class="menu-item active" @click="closeSidebarOnMobile()">
<i class="fas fa-home"></i>
<span>Dashboard</span>
</a>
</li>
<li>
<a href="#scan" class="menu-item" @click="closeSidebarOnMobile()">
<i class="fas fa-qrcode"></i>
<span>Scan QR</span>
</a>
</li>
<li>
<a href="#activity" class="menu-item" @click="closeSidebarOnMobile()">
<i class="fas fa-clock-rotate-left"></i>
<span>History</span>
</a>
</li>
<li>
<a href="#profile" class="menu-item" @click="closeSidebarOnMobile()">
<i class="fas fa-user"></i>
<span>Profile</span>
</a>
</li>
</ul>
</nav>

<div class="sidebar-footer">
<div class="user-avatar">SM</div>
<div>
<h5>Sarah Mthembu</h5>
<p>sarah@clockit.app</p>
</div>
<a href="logout.php" class="logout-button" aria-label="Log out">
<i class="fas fa-right-from-bracket"></i>
<span>Log out</span>
</a>
</div>

</aside>

<div
class="sidebar-overlay"
x-show="sidebarOpen && isMobile"
x-transition.opacity
@click="sidebarOpen=false"
x-cloak
></div>
