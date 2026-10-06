<div
    x-data="{ loaded: false }"
    x-init="() => {
        const loadEvent = 'onpagehide' in window ? 'pageshow' : 'load';

        window.addEventListener(loadEvent, () => {
            loaded = true;
            $refs.loadingAbsoluteContent.remove();
            document.querySelectorAll('[data-modal-handle]').forEach(item => item.classList.remove('invisible'));
        });
    }"
    x-ref="loadingAbsoluteContent"
    class="absolute w-full lg:flex items-start justify-center bg-body top-0 bottom-0 left-0 right-0 z-50"
    style="z-index: 60;"
    <div class="flex flex-col items-center justify-center p-8 mt-12">
        <div class="w-12 h-12 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin"></div>
        <span class="mt-3 text-xs font-semibold text-gray-500">Loading...</span>
    </div>
<!--data attr added because for none vue.js pages-->
