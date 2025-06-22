<aside x-data="{ sidebarOpen: false }" :class="{ 'w-screen' : sidebarOpen, 'pointer-events-none lg:pointer-events-auto' : !sidebarOpen }"  @open-sidebar.window="sidebarOpen = true" class="fixed z-40 w-64 md:z-50 lg:w-auto">
    {{-- Backdrop for mobile --}}
    <div x-show="sidebarOpen" @click="sidebarOpen=false" class="fixed top-0 right-0 z-40 w-screen h-screen duration-300 ease-out bg-black/20 dark:bg-white/10" x-cloak></div>

    <div :class="{ '-translate-x-full': !sidebarOpen }"
        class="relative z-50 flex flex-col justify-between w-64 h-screen overflow-scroll overflow-x-hidden -translate-x-full bg-white border-r border-black scrollbar-hide lg:translate-x-0">
        <div class="flex flex-col items-start p-6 sm:p-7">
            <a href="/">
                <x-logo class="w-auto mb-6 h-7"></x-logo>
            </a>
            <nav class="flex flex-col w-full -mx-1 space-y-2">
                <x-app.sidebar-link href="/dashboard" active="true">Dashboard</x-app.sidebar-link>
                <x-app.sidebar-link href="/playground" active="true">Playground</x-app.sidebar-link>

            </nav>
        </div>
        <div class="relative px-6 py-4">
            <x-app.user-menu />
        </div>
    </div>
</aside>