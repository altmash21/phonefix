<div
    x-data="{ loaded: false }"
    x-init="() => {
        const loadEvent = 'onpagehide' in window ? 'pageshow' : 'load';

        window.addEventListener(loadEvent, () => {
            loaded = true;
            $refs.loadingContent.remove();
            document.querySelectorAll('[data-modal-handle]').forEach(item => item.classList.remove('invisible'));
        });
    }"
    x-ref="loadingContent"
    class="fixed w-full lg:w-4/5 h-screen flex items-center justify-center bg-body top-0 bottom-0 ltr:right-0 rtl:left-0 -mx-1 z-50"
    style="z-index: 60;"
    <div class="flex flex-col items-center justify-center p-8 lg:-mt-16">
        <div class="w-12 h-12 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin"></div>
        <span class="mt-3 text-xs font-semibold text-gray-500">Loading...</span>
    </div>
<!--data attr added because for none vue.js pages-->
