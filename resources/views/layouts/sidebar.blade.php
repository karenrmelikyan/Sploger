<!--suppress CheckEmptyScriptTag, HtmlUnknownTag -->
<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <x-nav-item route="dashboard" icon="bi-house">
                {{ __('Dashboard') }}
            </x-nav-item>
            <x-nav-item route="projects.index" icon="bi-briefcase">
                {{ __('Projects') }}
            </x-nav-item>
            <x-nav-item route="splogs.index" icon="bi-globe">
                {{ __('Splogs') }}
            </x-nav-item>
            <x-nav-item route="keyword-sets.index" icon="bi-tags">
                {{ __('Keywords') }}
            </x-nav-item>
            <x-nav-item route="servers.index" icon="bi-server">
                {{ __('Servers') }}
            </x-nav-item>
            <x-nav-item route="users.index" icon="bi-people">
                {{ __('Users') }}
            </x-nav-item>
            <x-nav-item route="settings.index" icon="bi-gear">
                {{ __('Settings') }}
            </x-nav-item>
        </ul>
    </div>
</nav>
