<div class="sidebar-collector">
    <form action="{{ route('logout') }}" method="POST" class="logout-form">
        @csrf
        <button type="submit" class="logout-button">Log out</button>
    </form>
</div>
