// Sidebar functions
function openSidebar() {
    document.getElementById('sidebar')?.classList.remove('-translate-x-full');
}

function closeSidebar() {
    document.getElementById('sidebar')?.classList.add('-translate-x-full');
}

 // SIDEBAR TOGGLE
  function toggleSidebar() {

    const sidebar = document.getElementById('sidebar')

    if (sidebar.classList.contains('-translate-x-full')) {

      sidebar.classList.remove('-translate-x-full')
      sidebar.classList.add('translate-x-0')

    } else {

      sidebar.classList.add('-translate-x-full')
      sidebar.classList.remove('translate-x-0')
    }
  }