<footer class="footer">
    <div class="flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0">
        <div class="flex items-center justify-start space-x-3">
            <div>
                © {{ now()->year }} MJS Organic Affiliate Panel
            </div>
        </div>
        <a href="{{ route('home') }}" target="_blank" rel="noopener noreferrer"
            class="inline-flex items-center px-4 py-2 bg-black rounded-lg">
            <span class="text-white text-lg font-medium">MJS</span>
            <span class="text-green-400 text-lg font-black ml-1">Affiliate</span>
        </a>
    </div>
</footer>
