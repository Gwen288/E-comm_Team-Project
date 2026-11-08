
document.addEventListener('DOMContentLoaded', function () {

    // DUAL-HANDLE PRICE SLIDER

    const slider = document.querySelector('[data-dual-range]');

    if (slider) {

        const minInput = slider.querySelector('[data-min-range]');
        const maxInput = slider.querySelector('[data-max-range]');
        const selected = slider.querySelector('[data-range-selected]');

        const minLabel = document.querySelector('[data-min-label]');
        const maxLabel = document.querySelector('[data-max-label]');

        const floor = Number(slider.dataset.floor);
        const ceiling = Number(slider.dataset.ceiling);

        function updateRange(changed) {

            let min = Number(minInput.value);
            let max = Number(maxInput.value);

            if (max - min < 1) {

                if (changed === 'min') {
                    min = max - 1;
                } else {
                    max = min + 1;
                }
            }

            min = Math.max(floor, min);
            max = Math.min(ceiling, max);

            minInput.value = min;
            maxInput.value = max;

            const left = ((min - floor) / (ceiling - floor)) * 100;
            const right = ((max - floor) / (ceiling - floor)) * 100;

            selected.style.left = left + '%';
            selected.style.width = (right - left) + '%';

            minLabel.textContent = Math.round(min).toLocaleString();
            maxLabel.textContent = Math.round(max).toLocaleString();
        }

        minInput.addEventListener('input', function () {
            updateRange('min');
        });

        maxInput.addEventListener('input', function () {
            updateRange('max');
        });

        updateRange('');
    }


    // FEATURED PRODUCT SLIDESHOW

    const slideshow = document.querySelector('[data-slideshow]');

    if (slideshow) {

        const slides = Array.from(
            slideshow.querySelectorAll('[data-slide]')
        );

        const count = slideshow.querySelector('[data-slide-count]');

        let active = 0;

        function show(index) {

            active = (index + slides.length) % slides.length;

            slides.forEach(function (slide, slideIndex) {

                slide.hidden = slideIndex !== active;

                slide.classList.toggle(
                    'is-active',
                    slideIndex === active
                );

            });

            count.textContent =
                (active + 1) + ' / ' + slides.length;
        }

        slideshow.querySelector('[data-slide-prev]')
            .addEventListener('click', function () {
                show(active - 1);
            });

        slideshow.querySelector('[data-slide-next]')
            .addEventListener('click', function () {
                show(active + 1);
            });

        if (slides.length > 1) {

            window.setInterval(function () {
                show(active + 1);
            }, 6000);

        }
    }

});
