{{-- Popup «Hôm nay ăn gì» — tính năng phụ trên Kho --}}
@php
    $wteUser = auth('web')->user()?->loadMissing('profile');
    $wteCalorieMeta = app(\App\Services\DailyCalorieEstimator::class)->forUser($wteUser);
    $wteConfig = [
        'suggestUrl' => route('what-to-eat.suggest'),
        'detailUrlTemplate' => url('/what-to-eat/dishes/__SLUG__'),
        'contributeUrlTemplate' => url('/what-to-eat/dishes/__SLUG__/contributions'),
        'chooseUrl' => route('what-to-eat.choose'),
        'historyUrl' => route('what-to-eat.history'),
        'profileEditUrl' => route('profile.edit'),
        'csrf' => csrf_token(),
        'calorieMeta' => $wteCalorieMeta,
        'labels' => [
            'title' => __('what_to_eat.title'),
            'subtitle' => __('what_to_eat.subtitle'),
            'slot_label' => __('what_to_eat.slot_label'),
            'slot_breakfast' => __('what_to_eat.slot_breakfast'),
            'slot_lunch' => __('what_to_eat.slot_lunch'),
            'slot_dinner' => __('what_to_eat.slot_dinner'),
            'size_label' => __('what_to_eat.size_label'),
            'size_light' => __('what_to_eat.size_light'),
            'size_main' => __('what_to_eat.size_main'),
            'mode_label' => __('what_to_eat.mode_label'),
            'mode_dine_out' => __('what_to_eat.mode_dine_out'),
            'mode_cook_home' => __('what_to_eat.mode_cook_home'),
            'count_label' => __('what_to_eat.count_label'),
            'target_cal_label' => __('what_to_eat.target_cal_label'),
            'target_cal_hint' => __('what_to_eat.target_cal_hint'),
            'target_cal_from_weight' => __('what_to_eat.target_cal_from_weight', [
                'kcal' => $wteCalorieMeta['target_calories'],
                'kg' => $wteCalorieMeta['weight_kg'] ?? '—',
            ]),
            'target_cal_no_weight' => __('what_to_eat.target_cal_no_weight'),
            'target_cal_profile_link' => __('what_to_eat.target_cal_profile_link'),
            'meal_budget_hint' => __('what_to_eat.meal_budget_hint', ['kcal' => '__V__']),
            'suggest' => __('what_to_eat.suggest'),
            'suggest_again' => __('what_to_eat.suggest_again'),
            'close' => __('what_to_eat.close'),
            'back' => __('what_to_eat.back'),
            'detail' => __('what_to_eat.detail'),
            'choose' => __('what_to_eat.choose'),
            'chosen' => __('what_to_eat.chosen'),
            'contribute' => __('what_to_eat.contribute'),
            'contribute_submit' => __('what_to_eat.contribute_submit'),
            'history' => __('what_to_eat.history'),
            'places' => __('what_to_eat.places'),
            'places_empty' => __('what_to_eat.places_empty'),
            'community' => __('what_to_eat.community'),
            'loading' => __('what_to_eat.loading'),
            'loading_detail' => __('what_to_eat.loading_detail'),
            'results' => __('what_to_eat.results'),
            'kcal' => __('what_to_eat.kcal', ['value' => '__V__']),
            'grams' => __('what_to_eat.grams', ['value' => '__V__']),
            'kcal_per_100g' => __('what_to_eat.kcal_per_100g', ['value' => '__V__']),
            'calories_calc_title' => __('what_to_eat.calories_calc_title'),
            'calories_calc_hint' => __('what_to_eat.calories_calc_hint'),
            'portion_grams' => __('what_to_eat.portion_grams'),
            'portion_kcal' => __('what_to_eat.portion_kcal'),
            'cook_minutes' => __('what_to_eat.cook_minutes', ['value' => '__V__']),
            'benefits' => __('what_to_eat.benefits'),
            'harms' => __('what_to_eat.harms'),
            'advice' => __('what_to_eat.advice'),
            'notes' => __('what_to_eat.notes'),
            'ingredients' => __('what_to_eat.ingredients'),
            'steps' => __('what_to_eat.steps'),
            'no_recipe' => __('what_to_eat.no_recipe'),
            'disclaimer' => __('what_to_eat.disclaimer'),
            'error_generic' => __('what_to_eat.error_generic'),
            'error_detail' => __('what_to_eat.error_detail'),
            'error_contribute' => __('what_to_eat.error_contribute'),
            'meta_slots' => __('what_to_eat.meta_slots'),
            'contrib_type' => __('what_to_eat.contrib_type'),
            'contrib_recipe' => __('what_to_eat.contrib_recipe'),
            'contrib_calories' => __('what_to_eat.contrib_calories'),
            'contrib_harm' => __('what_to_eat.contrib_harm'),
            'contrib_benefit' => __('what_to_eat.contrib_benefit'),
            'contrib_advice' => __('what_to_eat.contrib_advice'),
            'contrib_note' => __('what_to_eat.contrib_note'),
            'contrib_element' => __('what_to_eat.contrib_element'),
            'element_wood' => __('what_to_eat.element_wood'),
            'element_fire' => __('what_to_eat.element_fire'),
            'element_earth' => __('what_to_eat.element_earth'),
            'element_metal' => __('what_to_eat.element_metal'),
            'element_water' => __('what_to_eat.element_water'),
        ],
        'defaults' => [
            'meal_slot' => 'lunch',
            'meal_size' => 'main',
            'meal_mode' => 'dine_out',
            'count' => 3,
            'target_calories' => $wteCalorieMeta['target_calories'],
        ],
    ];
@endphp

<div
    x-data="whatToEatModal(@js($wteConfig))"
    @keydown.escape.window="if (open) close()"
    class="contents"
>
    <button
        type="button"
        @click="openModal()"
        class="rounded-2xl bg-gradient-to-br from-amber-50 to-orange-50/80 px-4 py-3 text-left ring-1 ring-amber-200/80 transition hover:ring-amber-400 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500"
    >
        <div class="flex items-center gap-2">
            <span class="text-lg" aria-hidden="true">🍜</span>
            <div class="text-sm font-semibold text-stone-900">{{ __('what_to_eat.trigger_label') }}</div>
        </div>
        <div class="mt-0.5 text-xs text-stone-500">{{ __('what_to_eat.trigger_hint') }}</div>
    </button>

    <template x-teleport="body">
        <div
            x-show="open"
            x-cloak
            class="fixed inset-0 z-[80] flex items-end justify-center sm:items-center sm:p-4"
            role="dialog"
            aria-modal="true"
            :aria-label="labels.title"
        >
            <div class="absolute inset-0 bg-stone-900/40 backdrop-blur-[2px]" @click="close()"
                 x-show="open" x-transition.opacity.duration.200ms></div>

            <div class="relative z-10 flex max-h-[min(92dvh,740px)] w-full max-w-lg flex-col overflow-hidden rounded-t-3xl border border-stone-200/80 bg-white shadow-2xl sm:rounded-3xl"
                 @click.stop
                 x-show="open"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="translate-y-8 opacity-0 sm:scale-95"
                 x-transition:enter-end="translate-y-0 opacity-100 sm:scale-100">

                <div class="flex shrink-0 items-start justify-between gap-3 border-b border-stone-100 px-5 py-4">
                    <div class="min-w-0">
                        <template x-if="view === 'form' || view === 'results'">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">Utility</p>
                                <h2 class="mt-0.5 text-lg font-bold tracking-tight text-stone-900" x-text="labels.title"></h2>
                                <p class="mt-0.5 text-xs text-stone-500" x-text="labels.subtitle"></p>
                            </div>
                        </template>
                        <template x-if="view === 'detail' || view === 'contribute'">
                            <div>
                                <button type="button" @click="backToResults()" class="mb-1 inline-flex items-center gap-1 text-xs font-medium text-teal-700">
                                    <span>←</span> <span x-text="labels.back"></span>
                                </button>
                                <h2 class="text-lg font-bold tracking-tight text-stone-900">
                                    <span x-text="(detail && detail.emoji) || '🍽️'"></span>
                                    <span x-text="(detail && detail.name) || ''"></span>
                                </h2>
                            </div>
                        </template>
                    </div>
                    <div class="flex shrink-0 items-center gap-1">
                        <a :href="historyUrl" class="rounded-full px-2.5 py-1.5 text-[11px] font-medium text-teal-700 hover:bg-teal-50" x-text="labels.history"></a>
                        <button type="button" @click="close()" class="flex h-10 w-10 items-center justify-center rounded-full bg-stone-100 text-stone-600 hover:bg-stone-200" :aria-label="labels.close">✕</button>
                    </div>
                </div>

                <div class="min-h-0 flex-1 overflow-y-auto px-5 py-4">
                    {{-- FORM --}}
                    <div x-show="view === 'form'" class="space-y-5">
                        <p x-show="error" x-text="error" x-cloak class="rounded-xl bg-red-50 px-3 py-2 text-xs text-red-800"></p>
                        <fieldset>
                            <legend class="text-sm font-semibold text-stone-800" x-text="labels.slot_label"></legend>
                            <div class="mt-2 grid grid-cols-3 gap-2">
                                <template x-for="opt in slotOptions" :key="opt.value">
                                    <button type="button" @click="meal_slot = opt.value"
                                            class="rounded-2xl px-2 py-2.5 text-sm font-medium ring-1 transition"
                                            :class="meal_slot === opt.value ? 'bg-teal-600 text-white ring-teal-600' : 'bg-stone-50 text-stone-700 ring-stone-200'"
                                            x-text="opt.label"></button>
                                </template>
                            </div>
                        </fieldset>
                        <fieldset>
                            <legend class="text-sm font-semibold text-stone-800" x-text="labels.size_label"></legend>
                            <div class="mt-2 grid grid-cols-2 gap-2">
                                <template x-for="opt in sizeOptions" :key="opt.value">
                                    <button type="button" @click="meal_size = opt.value"
                                            class="rounded-2xl px-2 py-2.5 text-sm font-medium ring-1 transition"
                                            :class="meal_size === opt.value ? 'bg-teal-600 text-white ring-teal-600' : 'bg-stone-50 text-stone-700 ring-stone-200'"
                                            x-text="opt.label"></button>
                                </template>
                            </div>
                        </fieldset>
                        <fieldset>
                            <legend class="text-sm font-semibold text-stone-800" x-text="labels.mode_label"></legend>
                            <div class="mt-2 grid grid-cols-2 gap-2">
                                <template x-for="opt in modeOptions" :key="opt.value">
                                    <button type="button" @click="meal_mode = opt.value"
                                            class="rounded-2xl px-2 py-2.5 text-sm font-medium ring-1 transition"
                                            :class="meal_mode === opt.value ? 'bg-teal-600 text-white ring-teal-600' : 'bg-stone-50 text-stone-700 ring-stone-200'"
                                            x-text="opt.label"></button>
                                </template>
                            </div>
                        </fieldset>
                        <fieldset>
                            <legend class="text-sm font-semibold text-stone-800" x-text="labels.count_label"></legend>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <template x-for="n in [1,2,3,4,5]" :key="n">
                                    <button type="button" @click="count = n"
                                            class="flex h-11 w-11 items-center justify-center rounded-full text-sm font-semibold ring-1"
                                            :class="count === n ? 'bg-amber-500 text-white ring-amber-500' : 'bg-stone-50 text-stone-700 ring-stone-200'"
                                            x-text="n"></button>
                                </template>
                            </div>
                        </fieldset>

                        <fieldset class="rounded-2xl bg-amber-50/60 p-3 ring-1 ring-amber-100">
                            <legend class="px-1 text-sm font-semibold text-stone-800" x-text="labels.target_cal_label"></legend>
                            <p class="mt-1 text-[11px] leading-relaxed text-stone-500" x-text="labels.target_cal_hint"></p>
                            <p class="mt-1 text-[11px] text-teal-800"
                               x-text="calorieMeta.has_body_metrics ? labels.target_cal_from_weight : labels.target_cal_no_weight"></p>
                            <a x-show="!calorieMeta.has_body_metrics" :href="profileEditUrl"
                               class="mt-1 inline-block text-[11px] font-medium text-teal-700 underline"
                               x-text="labels.target_cal_profile_link"></a>

                            <div class="mt-2.5 flex flex-wrap gap-2">
                                <template x-for="p in caloriePresets" :key="p">
                                    <button type="button" @click="target_calories = p"
                                            class="rounded-full px-3 py-1.5 text-xs font-semibold ring-1 transition"
                                            :class="Number(target_calories) === p ? 'bg-amber-500 text-white ring-amber-500' : 'bg-white text-stone-700 ring-stone-200 hover:ring-amber-300'"
                                            x-text="p"></button>
                                </template>
                            </div>
                            <div class="mt-2 flex items-center gap-2">
                                <input type="number"
                                       x-model.number="target_calories"
                                       min="1000" max="5000" step="50"
                                       class="w-full rounded-xl border border-stone-300 bg-white px-3 py-2 text-sm font-semibold tabular-nums focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-200"
                                       :placeholder="defaults.target_calories">
                                <span class="shrink-0 text-xs font-medium text-stone-500">kcal/ngày</span>
                            </div>
                            <p class="mt-1.5 text-[11px] text-stone-500"
                               x-text="mealBudgetLabel()"></p>
                        </fieldset>
                    </div>

                    <div x-show="loading" x-cloak class="flex flex-col items-center gap-3 py-12 text-sm text-stone-500">
                        <span class="inline-block h-8 w-8 animate-spin rounded-full border-2 border-teal-200 border-t-teal-600"></span>
                        <span x-text="labels.loading"></span>
                    </div>

                    {{-- RESULTS --}}
                    <div x-show="view === 'results' && !loading" x-cloak class="space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="text-sm font-semibold text-stone-800" x-text="labels.results"></h3>
                            <button type="button" @click="view = 'form'; dishes = []; metaMessage = null; error = null"
                                    class="text-xs font-medium text-teal-700">Đổi lựa chọn</button>
                        </div>
                        <p x-show="metaMessage" x-text="metaMessage" class="rounded-xl bg-amber-50 px-3 py-2 text-xs text-amber-900"></p>
                        <p x-show="error" x-text="error" class="rounded-xl bg-red-50 px-3 py-2 text-xs text-red-800"></p>
                        <ul class="space-y-2.5">
                            <template x-for="dish in dishes" :key="dish.id">
                                <li class="rounded-2xl bg-stone-50/90 p-3 ring-1 ring-stone-200/80">
                                    <div class="flex items-stretch gap-3">
                                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white text-2xl shadow-sm" x-text="dish.emoji"></div>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate font-semibold text-stone-900" x-text="dish.name"></p>
                                            <p class="mt-0.5 line-clamp-2 text-xs text-stone-500" x-text="dish.reason"></p>
                                            <p x-show="dish.calories_basis_label" class="mt-0.5 text-[11px] tabular-nums text-stone-400" x-text="dish.calories_basis_label"></p>
                                            <p x-show="dish.places_count > 0" class="mt-1 text-[11px] text-teal-700" x-text="(dish.places_count || 0) + ' quán/trải nghiệm gợi ý'"></p>
                                        </div>
                                    </div>
                                    <div class="mt-2.5 flex flex-wrap gap-2">
                                        <button type="button" @click="openDetail(dish)"
                                                class="rounded-full bg-white px-3 py-1.5 text-xs font-semibold text-teal-700 ring-1 ring-teal-200 hover:bg-teal-50"
                                                x-text="labels.detail"></button>
                                        <button type="button" @click="chooseDish(dish)"
                                                class="rounded-full bg-teal-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-teal-700"
                                                x-text="chosenId === dish.id ? labels.chosen : labels.choose"></button>
                                    </div>
                                </li>
                            </template>
                        </ul>
                    </div>

                    {{-- DETAIL --}}
                    <div x-show="view === 'detail'" x-cloak>
                        <div x-show="detailLoading" class="flex flex-col items-center gap-3 py-12 text-sm text-stone-500">
                            <span class="inline-block h-8 w-8 animate-spin rounded-full border-2 border-teal-200 border-t-teal-600"></span>
                            <span x-text="labels.loading_detail"></span>
                        </div>
                        <div x-show="!detailLoading && detail" class="space-y-4">
                            <p class="text-sm leading-relaxed text-stone-600" x-text="detail.summary"></p>
                            <div class="flex flex-wrap gap-2">
                                <span x-show="detail.calories_basis_label" class="rounded-full bg-stone-100 px-2.5 py-1 text-xs" x-text="detail.calories_basis_label"></span>
                                <span x-show="detail.kcal_per_100g != null" class="rounded-full bg-stone-100 px-2.5 py-1 text-xs" x-text="fmtKcalPer100(detail.kcal_per_100g)"></span>
                                <span x-show="detail.cook_minutes" class="rounded-full bg-stone-100 px-2.5 py-1 text-xs" x-text="fmtCook(detail.cook_minutes)"></span>
                                <span x-show="detail.five_element_label" class="rounded-full bg-amber-50 px-2.5 py-1 text-xs ring-1 ring-amber-100">
                                    <span x-text="detail.five_element_emoji"></span>
                                    <span x-text="detail.five_element_label"></span>
                                </span>
                            </div>

                            {{-- Bộ tính calo theo khối lượng --}}
                            <section x-show="detail.has_calorie_basis" x-cloak
                                     class="rounded-2xl border border-teal-100 bg-gradient-to-br from-teal-50/80 to-white p-4 ring-1 ring-teal-100/80">
                                <h3 class="text-sm font-semibold text-teal-900" x-text="labels.calories_calc_title"></h3>
                                <p class="mt-0.5 text-[11px] leading-relaxed text-teal-800/80" x-text="labels.calories_calc_hint"></p>

                                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <label class="text-xs font-medium text-stone-600" x-text="labels.portion_grams"></label>
                                        <div class="mt-1 flex items-center gap-1.5">
                                            <button type="button" @click="nudgeGrams(-portionStep)"
                                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white text-lg font-semibold text-stone-700 ring-1 ring-stone-200 hover:bg-stone-50"
                                                    aria-label="-">−</button>
                                            <input type="number"
                                                   x-model.number="portionGrams"
                                                   @input="syncFromGrams()"
                                                   :min="detail.portion_grams_min || 10"
                                                   :max="detail.portion_grams_max || 2000"
                                                   step="10"
                                                   class="w-full rounded-xl border border-stone-300 px-3 py-2 text-center text-sm font-semibold tabular-nums focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-200">
                                            <button type="button" @click="nudgeGrams(portionStep)"
                                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white text-lg font-semibold text-stone-700 ring-1 ring-stone-200 hover:bg-stone-50"
                                                    aria-label="+">+</button>
                                        </div>
                                        <div class="mt-1.5 flex flex-wrap gap-1">
                                            <template x-for="g in portionPresets" :key="g">
                                                <button type="button" @click="portionGrams = g; syncFromGrams()"
                                                        class="rounded-full px-2 py-0.5 text-[10px] font-medium ring-1 transition"
                                                        :class="portionGrams === g ? 'bg-teal-600 text-white ring-teal-600' : 'bg-white text-stone-600 ring-stone-200'"
                                                        x-text="g + 'g'"></button>
                                            </template>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="text-xs font-medium text-stone-600" x-text="labels.portion_kcal"></label>
                                        <div class="mt-1 flex items-center gap-1.5">
                                            <button type="button" @click="nudgeKcal(-kcalStep)"
                                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white text-lg font-semibold text-stone-700 ring-1 ring-stone-200 hover:bg-stone-50"
                                                    aria-label="-">−</button>
                                            <input type="number"
                                                   x-model.number="portionKcal"
                                                   @input="syncFromKcal()"
                                                   min="0"
                                                   max="10000"
                                                   step="10"
                                                   class="w-full rounded-xl border border-stone-300 px-3 py-2 text-center text-sm font-semibold tabular-nums text-teal-800 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-200">
                                            <button type="button" @click="nudgeKcal(kcalStep)"
                                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white text-lg font-semibold text-stone-700 ring-1 ring-stone-200 hover:bg-stone-50"
                                                    aria-label="+">+</button>
                                        </div>
                                        <p class="mt-1.5 text-center text-xs text-stone-500">
                                            <span class="font-semibold tabular-nums text-teal-800" x-text="portionKcal"></span>
                                            kcal ≈
                                            <span class="font-semibold tabular-nums text-stone-800" x-text="portionGrams"></span> g
                                        </p>
                                    </div>
                                </div>
                            </section>

                            <section x-show="detail.benefits">
                                <h3 class="text-sm font-semibold text-teal-800" x-text="labels.benefits"></h3>
                                <p class="mt-1 text-sm text-stone-600" x-text="detail.benefits"></p>
                            </section>
                            <section x-show="detail.harms">
                                <h3 class="text-sm font-semibold text-rose-800" x-text="labels.harms"></h3>
                                <p class="mt-1 text-sm text-stone-600" x-text="detail.harms"></p>
                            </section>
                            <section x-show="detail.advice">
                                <h3 class="text-sm font-semibold text-stone-800" x-text="labels.advice"></h3>
                                <p class="mt-1 text-sm text-stone-600" x-text="detail.advice"></p>
                            </section>
                            <section x-show="detail.ingredients?.length">
                                <h3 class="text-sm font-semibold text-stone-800" x-text="labels.ingredients"></h3>
                                <ul class="mt-2 space-y-1">
                                    <template x-for="(ing, i) in (detail.ingredients || [])" :key="i">
                                        <li class="flex justify-between gap-2 rounded-xl bg-stone-50 px-3 py-2 text-sm">
                                            <span x-text="ing.name"></span>
                                            <span class="text-stone-500" x-text="ing.amount"></span>
                                        </li>
                                    </template>
                                </ul>
                            </section>
                            <section x-show="detail.steps?.length">
                                <h3 class="text-sm font-semibold text-stone-800" x-text="labels.steps"></h3>
                                <ol class="mt-2 list-decimal space-y-1.5 pl-5 text-sm text-stone-600">
                                    <template x-for="(step, i) in (detail.steps || [])" :key="i">
                                        <li x-text="step"></li>
                                    </template>
                                </ol>
                            </section>
                            <p x-show="!detail.has_recipe" class="rounded-xl bg-stone-50 px-3 py-2 text-xs text-stone-500" x-text="labels.no_recipe"></p>

                            <section>
                                <h3 class="text-sm font-semibold text-stone-800" x-text="labels.places"></h3>
                                <ul x-show="detail.places?.length" class="mt-2 space-y-2">
                                    <template x-for="p in (detail.places || [])" :key="p.id">
                                        <li>
                                            <a :href="p.url" class="block rounded-2xl bg-teal-50/80 px-3 py-2.5 ring-1 ring-teal-100 transition hover:bg-teal-50">
                                                <p class="text-sm font-semibold text-teal-900" x-text="p.place_name || p.title"></p>
                                                <p class="text-xs text-teal-800/80" x-text="p.address || p.title"></p>
                                                <p x-show="p.distance_km != null" class="text-[11px] text-teal-700" x-text="p.distance_km + ' km'"></p>
                                            </a>
                                        </li>
                                    </template>
                                </ul>
                                <p x-show="!detail.places?.length" class="mt-1 text-xs text-stone-500" x-text="labels.places_empty"></p>
                            </section>

                            <section x-show="detail.community?.length">
                                <h3 class="text-sm font-semibold text-stone-800" x-text="labels.community"></h3>
                                <ul class="mt-2 space-y-2">
                                    <template x-for="c in (detail.community || [])" :key="c.id">
                                        <li class="rounded-xl bg-stone-50 px-3 py-2 text-xs text-stone-600 ring-1 ring-stone-100">
                                            <span class="font-semibold text-stone-800" x-text="c.type_label"></span>
                                            <span x-show="c.author"> · <span x-text="c.author?.name"></span></span>
                                        </li>
                                    </template>
                                </ul>
                            </section>

                            <p class="rounded-2xl border border-amber-100 bg-amber-50/80 px-3 py-2.5 text-[11px] leading-relaxed text-amber-950/80" x-text="labels.disclaimer"></p>
                        </div>
                    </div>

                    {{-- CONTRIBUTE --}}
                    <div x-show="view === 'contribute'" x-cloak class="space-y-4">
                        <p x-show="contribMessage" x-text="contribMessage" class="rounded-xl bg-teal-50 px-3 py-2 text-xs text-teal-900"></p>
                        <p x-show="error" x-text="error" class="rounded-xl bg-red-50 px-3 py-2 text-xs text-red-800"></p>
                        <div>
                            <label class="text-sm font-semibold text-stone-800" x-text="labels.contrib_type"></label>
                            <select x-model="contribType" class="mt-1.5 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm">
                                <option value="recipe" x-text="labels.contrib_recipe"></option>
                                <option value="calories" x-text="labels.contrib_calories"></option>
                                <option value="benefit" x-text="labels.contrib_benefit"></option>
                                <option value="harm" x-text="labels.contrib_harm"></option>
                                <option value="advice" x-text="labels.contrib_advice"></option>
                                <option value="note" x-text="labels.contrib_note"></option>
                                <option value="five_element" x-text="labels.contrib_element"></option>
                            </select>
                        </div>
                        <div x-show="contribType === 'calories'" class="space-y-2">
                            <div>
                                <label class="text-sm font-medium text-stone-700">Kcal / khẩu phần chuẩn</label>
                                <input type="number" x-model.number="contribKcal" min="1" max="5000" class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="text-sm font-medium text-stone-700">Khối lượng khẩu phần (gram)</label>
                                <input type="number" x-model.number="contribServingGrams" min="1" max="2000" class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm">
                            </div>
                            <p class="text-[11px] text-stone-500">Ví dụ: 450 kcal cho 400g → hệ thống quy đổi khi user chỉnh gram.</p>
                        </div>
                        <div x-show="contribType === 'five_element'">
                            <label class="text-sm font-medium text-stone-700">Ngũ hành</label>
                            <select x-model="contribElement" class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm">
                                <option value="wood" x-text="labels.element_wood"></option>
                                <option value="fire" x-text="labels.element_fire"></option>
                                <option value="earth" x-text="labels.element_earth"></option>
                                <option value="metal" x-text="labels.element_metal"></option>
                                <option value="water" x-text="labels.element_water"></option>
                            </select>
                            <textarea x-model="contribBody" rows="2" placeholder="Lý do (tuỳ chọn)" class="mt-2 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm"></textarea>
                        </div>
                        <div x-show="contribType === 'recipe'">
                            <label class="text-sm font-medium text-stone-700">Các bước (mỗi dòng 1 bước)</label>
                            <textarea x-model="contribStepsText" rows="5" class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm" placeholder="Bước 1&#10;Bước 2"></textarea>
                            <label class="mt-2 block text-sm font-medium text-stone-700">Phút nấu</label>
                            <input type="number" x-model.number="contribCookMinutes" min="1" max="600" class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm">
                        </div>
                        <div x-show="['benefit','harm','advice','note'].includes(contribType)">
                            <label class="text-sm font-medium text-stone-700">Nội dung</label>
                            <textarea x-model="contribBody" rows="4" class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm"></textarea>
                        </div>
                    </div>
                </div>

                <div class="shrink-0 border-t border-stone-100 bg-white/95 px-5 py-3 pb-[max(0.75rem,env(safe-area-inset-bottom))]">
                    <div class="flex flex-wrap gap-2" x-show="view === 'form'">
                        <button type="button" @click="suggest(false)" :disabled="loading" class="vivu-btn-primary flex-1 justify-center disabled:opacity-60">
                            <span x-text="labels.suggest"></span>
                        </button>
                    </div>
                    <div class="flex flex-wrap gap-2" x-show="view === 'results' && dishes.length > 0 && !loading" x-cloak>
                        <button type="button" @click="suggest(true)" :disabled="loading"
                                class="flex-1 rounded-full bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-amber-600">
                            <span x-text="labels.suggest_again"></span>
                        </button>
                        <button type="button" @click="close()" class="rounded-full bg-stone-100 px-4 py-2.5 text-sm font-semibold text-stone-700">
                            <span x-text="labels.close"></span>
                        </button>
                    </div>
                    <div class="flex flex-wrap gap-2" x-show="view === 'detail' && !detailLoading" x-cloak>
                        <button type="button" @click="view = 'contribute'; error = null; contribMessage = null"
                                class="flex-1 rounded-full bg-amber-50 px-4 py-2.5 text-sm font-semibold text-amber-900 ring-1 ring-amber-200">
                            <span x-text="labels.contribute"></span>
                        </button>
                        <button type="button" @click="backToResults()" class="rounded-full bg-stone-100 px-4 py-2.5 text-sm font-semibold text-stone-700">
                            <span x-text="labels.back"></span>
                        </button>
                    </div>
                    <div class="flex flex-wrap gap-2" x-show="view === 'contribute'" x-cloak>
                        <button type="button" @click="submitContribute()" :disabled="contribLoading"
                                class="vivu-btn-primary flex-1 justify-center disabled:opacity-60">
                            <span x-text="labels.contribute_submit"></span>
                        </button>
                        <button type="button" @click="view = 'detail'" class="rounded-full bg-stone-100 px-4 py-2.5 text-sm font-semibold text-stone-700">
                            <span x-text="labels.back"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                if (window.__vivuWhatToEatRegistered) return;
                window.__vivuWhatToEatRegistered = true;

                Alpine.data('whatToEatModal', (config = {}) => ({
                    open: false,
                    view: 'form',
                    loading: false,
                    detailLoading: false,
                    contribLoading: false,
                    meal_slot: config.defaults?.meal_slot ?? 'lunch',
                    meal_size: config.defaults?.meal_size ?? 'main',
                    meal_mode: config.defaults?.meal_mode ?? 'dine_out',
                    count: config.defaults?.count ?? 3,
                    target_calories: config.defaults?.target_calories ?? 2000,
                    defaults: config.defaults ?? {},
                    calorieMeta: config.calorieMeta ?? { presets: [1500, 2000, 2500], has_body_metrics: false },
                    caloriePresets: config.calorieMeta?.presets ?? [1500, 2000, 2500],
                    profileEditUrl: config.profileEditUrl ?? '/profile/edit',
                    lastMealBudget: null,
                    dishes: [],
                    detail: null,
                    activeDish: null,
                    error: null,
                    metaMessage: null,
                    logId: null,
                    chosenId: null,
                    lat: null,
                    lng: null,
                    contribType: 'benefit',
                    contribBody: '',
                    contribKcal: 300,
                    contribServingGrams: 350,
                    contribElement: 'earth',
                    contribStepsText: '',
                    contribCookMinutes: 20,
                    contribMessage: null,
                    portionGrams: 350,
                    portionKcal: 0,
                    portionStep: 25,
                    kcalStep: 25,
                    portionPresets: [100, 150, 200, 250, 350, 500],
                    suggestUrl: config.suggestUrl,
                    detailUrlTemplate: config.detailUrlTemplate,
                    contributeUrlTemplate: config.contributeUrlTemplate,
                    chooseUrl: config.chooseUrl,
                    historyUrl: config.historyUrl,
                    csrf: config.csrf,
                    labels: config.labels ?? {},

                    get slotOptions() {
                        return [
                            { value: 'breakfast', label: this.labels.slot_breakfast },
                            { value: 'lunch', label: this.labels.slot_lunch },
                            { value: 'dinner', label: this.labels.slot_dinner },
                        ];
                    },
                    get sizeOptions() {
                        return [
                            { value: 'light', label: this.labels.size_light },
                            { value: 'main', label: this.labels.size_main },
                        ];
                    },
                    get modeOptions() {
                        return [
                            { value: 'dine_out', label: this.labels.mode_dine_out },
                            { value: 'cook_home', label: this.labels.mode_cook_home },
                        ];
                    },

                    mealBudgetLabel() {
                        const daily = Number(this.target_calories) || 2000;
                        const slotPct = { breakfast: 0.25, lunch: 0.35, dinner: 0.40 }[this.meal_slot] ?? 0.35;
                        let pct = slotPct;
                        if (this.meal_size === 'light') pct *= 0.55;
                        const budget = Math.max(80, Math.round(daily * pct));
                        return (this.labels.meal_budget_hint || '≈ :value kcal cho bữa này')
                            .replace('__V__', budget)
                            .replace(':kcal', budget)
                            .replace(':value', budget);
                    },

                    openModal() {
                        this.open = true;
                        this.view = 'form';
                        this.error = null;
                        this.metaMessage = null;
                        this.detail = null;
                        this.chosenId = null;
                        document.body.classList.add('overflow-hidden');
                        this.captureLocation();
                    },
                    close() {
                        this.open = false;
                        document.body.classList.remove('overflow-hidden');
                    },
                    backToResults() {
                        this.view = this.dishes.length ? 'results' : 'form';
                        this.error = null;
                    },
                    captureLocation() {
                        if (!navigator.geolocation) return;
                        navigator.geolocation.getCurrentPosition(
                            (pos) => {
                                this.lat = pos.coords.latitude;
                                this.lng = pos.coords.longitude;
                            },
                            () => {},
                            { maximumAge: 600000, timeout: 5000 },
                        );
                    },
                    fmtKcal(v) {
                        return (this.labels.kcal || ':value kcal').replace('__V__', v).replace(':value', v);
                    },
                    fmtKcalPer100(v) {
                        return (this.labels.kcal_per_100g || ':value kcal/100g').replace('__V__', v).replace(':value', v);
                    },
                    fmtCook(v) {
                        return (this.labels.cook_minutes || '~:value phút').replace('__V__', v).replace(':value', v);
                    },
                    initPortionFromDetail() {
                        if (!this.detail?.has_calorie_basis) {
                            this.portionGrams = this.detail?.serving_grams || 350;
                            this.portionKcal = this.detail?.calories_kcal || 0;
                            return;
                        }
                        this.portionGrams = this.detail.serving_grams;
                        this.portionKcal = this.detail.calories_kcal;
                    },
                    clampGrams(g) {
                        const min = this.detail?.portion_grams_min || 10;
                        const max = this.detail?.portion_grams_max || 2000;
                        return Math.max(min, Math.min(max, Math.round(Number(g) || min)));
                    },
                    syncFromGrams() {
                        if (!this.detail?.has_calorie_basis) return;
                        this.portionGrams = this.clampGrams(this.portionGrams);
                        const baseK = this.detail.calories_kcal;
                        const baseG = this.detail.serving_grams;
                        this.portionKcal = Math.max(0, Math.round(baseK * (this.portionGrams / baseG)));
                    },
                    syncFromKcal() {
                        if (!this.detail?.has_calorie_basis) return;
                        const kcal = Math.max(0, Math.round(Number(this.portionKcal) || 0));
                        this.portionKcal = kcal;
                        const baseK = this.detail.calories_kcal;
                        const baseG = this.detail.serving_grams;
                        if (baseK <= 0) return;
                        this.portionGrams = this.clampGrams(Math.round(baseG * (kcal / baseK)));
                    },
                    nudgeGrams(delta) {
                        this.portionGrams = this.clampGrams((this.portionGrams || 0) + delta);
                        this.syncFromGrams();
                    },
                    nudgeKcal(delta) {
                        this.portionKcal = Math.max(0, (this.portionKcal || 0) + delta);
                        this.syncFromKcal();
                    },
                    async suggest(reroll = false) {
                        this.loading = true;
                        this.error = null;
                        this.metaMessage = null;
                        this.view = 'results';
                        const exclude_ids = reroll ? this.dishes.map((d) => d.id) : [];
                        try {
                            const body = {
                                meal_slot: this.meal_slot,
                                meal_size: this.meal_size,
                                meal_mode: this.meal_mode,
                                count: this.count,
                                target_calories: this.target_calories || this.defaults.target_calories || 2000,
                                exclude_ids,
                            };
                            if (this.lat != null && this.lng != null) {
                                body.lat = this.lat;
                                body.lng = this.lng;
                            }
                            const res = await fetch(this.suggestUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': this.csrf,
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                body: JSON.stringify(body),
                            });
                            const json = await res.json().catch(() => ({}));
                            if (!res.ok) {
                                this.error = json.message || (json.errors && Object.values(json.errors).flat()[0]) || this.labels.error_generic;
                                this.dishes = [];
                                this.view = 'form';
                                return;
                            }
                            this.dishes = json.data || [];
                            this.metaMessage = json.meta?.message || null;
                            this.logId = json.meta?.log_id || null;
                            this.lastMealBudget = json.meta?.meal_budget ?? null;
                            this.chosenId = null;
                            if (!this.dishes.length) {
                                this.view = 'form';
                                this.error = json.meta?.message || this.labels.error_generic;
                            }
                        } catch (e) {
                            this.error = this.labels.error_generic;
                            this.view = 'form';
                        } finally {
                            this.loading = false;
                        }
                    },
                    async openDetail(dish) {
                        this.activeDish = dish;
                        this.view = 'detail';
                        this.detailLoading = true;
                        this.detail = { emoji: dish.emoji, name: dish.name };
                        this.error = null;
                        let url = this.detailUrlTemplate.replace('__SLUG__', encodeURIComponent(dish.slug));
                        if (this.lat != null && this.lng != null) {
                            url += `?lat=${this.lat}&lng=${this.lng}`;
                        }
                        try {
                            const res = await fetch(url, {
                                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                            });
                            const json = await res.json().catch(() => ({}));
                            if (!res.ok) {
                                this.error = this.labels.error_detail;
                                this.detail = null;
                                return;
                            }
                            this.detail = json.data;
                            this.initPortionFromDetail();
                        } catch (e) {
                            this.error = this.labels.error_detail;
                            this.detail = null;
                        } finally {
                            this.detailLoading = false;
                        }
                    },
                    async chooseDish(dish) {
                        if (!this.logId) {
                            this.chosenId = dish.id;
                            return;
                        }
                        try {
                            const res = await fetch(this.chooseUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': this.csrf,
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                body: JSON.stringify({ log_id: this.logId, dish_id: dish.id }),
                            });
                            if (res.ok) this.chosenId = dish.id;
                        } catch (e) {}
                    },
                    async submitContribute() {
                        if (!this.activeDish) return;
                        this.contribLoading = true;
                        this.error = null;
                        this.contribMessage = null;
                        let payload = {};
                        if (this.contribType === 'calories') {
                            payload = {
                                kcal_per_serving: this.contribKcal,
                                serving_grams: this.contribServingGrams || 100,
                            };
                        } else if (this.contribType === 'five_element') {
                            payload = { element: this.contribElement, rationale: this.contribBody };
                        } else if (this.contribType === 'recipe') {
                            const steps = this.contribStepsText.split('\n').map((s) => s.trim()).filter(Boolean);
                            payload = { steps, cook_minutes: this.contribCookMinutes, ingredients: [] };
                        } else {
                            payload = { body: this.contribBody };
                        }
                        const url = this.contributeUrlTemplate.replace('__SLUG__', encodeURIComponent(this.activeDish.slug));
                        try {
                            const res = await fetch(url, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': this.csrf,
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                body: JSON.stringify({ type: this.contribType, payload }),
                            });
                            const json = await res.json().catch(() => ({}));
                            if (!res.ok) {
                                this.error = json.message || (json.errors && Object.values(json.errors).flat()[0]) || this.labels.error_contribute;
                                return;
                            }
                            this.contribMessage = json.meta?.message || 'OK';
                            this.contribBody = '';
                            this.contribStepsText = '';
                        } catch (e) {
                            this.error = this.labels.error_contribute;
                        } finally {
                            this.contribLoading = false;
                        }
                    },
                }));
            });
        </script>
    @endpush
@endonce
