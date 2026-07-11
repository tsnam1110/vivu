{{-- Alpine component: experienceForm — create / edit --}}
<script>
window.onGoogleMapsReady = window.onGoogleMapsReady || function () {
    window.dispatchEvent(new CustomEvent('google-maps-ready'));
};

document.addEventListener('alpine:init', () => {
    if (window.__vivuExperienceFormRegistered) {
        return;
    }
    window.__vivuExperienceFormRegistered = true;

    Alpine.data('experienceForm', (config = {}) => ({
        address: config.address ?? '',
        latitude: String(config.latitude ?? '16.0544'),
        longitude: String(config.longitude ?? '108.2022'),
        placeId: config.placeId ?? '',
        placeName: config.placeName ?? '',
        categoryId: config.categoryId ? String(config.categoryId) : '',
        title: config.title ?? '',
        content: config.content ?? '',
        // authorRating: 1–10 (mỗi đơn vị = ½ sao; 10 = 5 sao đầy)
        authorRating: config.authorRating ? Number(config.authorRating) : null,
        hoverRating: 0,
        ratingPulse: null,
        selectedTags: Array.isArray(config.selectedTags) ? config.selectedTags.map(Number) : [],
        customTags: Array.isArray(config.customTags) ? [...config.customTags] : [],
        tagQuery: '',
        status: config.status ?? 'published',
        allTags: config.tags ?? [],
        hasMaps: !!config.hasMaps,
        enableImages: config.enableImages !== false,
        mapReady: false,
        map: null,
        marker: null,
        autocomplete: null,
        locating: false,
        locationError: '',
        mapHint: '',
        previews: [],
        files: [],
        coverIndex: 0,
        submitting: false,
        clientError: '',

        get visibleTags() {
            if (!this.categoryId) {
                return [];
            }
            const catId = Number(this.categoryId);
            return this.allTags.filter((t) => t.category_id === null || Number(t.category_id) === catId);
        },

        get filteredTags() {
            const q = this.tagQuery.trim().toLowerCase();
            let list = this.visibleTags;
            if (q) {
                list = list.filter((t) => t.name.toLowerCase().includes(q));
            }
            // Ưu tiên đã chọn, rồi usage (đã sort server)
            const selected = new Set(this.selectedTags);
            return list
                .slice()
                .sort((a, b) => {
                    const as = selected.has(a.id) ? 0 : 1;
                    const bs = selected.has(b.id) ? 0 : 1;
                    if (as !== bs) return as - bs;
                    return 0;
                })
                .slice(0, q ? 24 : 12);
        },

        get canCreateTag() {
            const q = this.tagQuery.trim();
            if (!q || !this.categoryId) return false;
            if (this.selectedCount >= 10) return false;
            const lower = q.toLowerCase();
            const existsApproved = this.visibleTags.some((t) => t.name.toLowerCase() === lower);
            const existsCustom = this.customTags.some((t) => t.toLowerCase() === lower);
            return !existsApproved && !existsCustom;
        },

        get selectedCount() {
            return this.selectedTags.length + this.customTags.length;
        },

        init() {
            this.$watch('categoryId', () => {
                this.pruneTags();
                this.tagQuery = '';
            });
            if (this.hasMaps) {
                window.addEventListener('google-maps-ready', () => this.initMap());
                if (window.google?.maps) {
                    this.initMap();
                }
            }
        },

        pruneTags() {
            const allowed = new Set(this.visibleTags.map((t) => Number(t.id)));
            this.selectedTags = this.selectedTags.filter((id) => allowed.has(Number(id)));
        },

        selectCategory(id) {
            this.categoryId = String(id);
        },

        /**
         * @param {number} n 1–10 (½ sao mỗi bước)
         */
        setRating(n) {
            n = Math.max(1, Math.min(10, Number(n)));
            // Click lại đúng mức hiện tại → bỏ đánh giá
            if (this.authorRating === n) {
                this.authorRating = null;
                this.ratingPulse = null;
                return;
            }
            this.authorRating = n;
            this.ratingPulse = n;
            setTimeout(() => {
                if (this.ratingPulse === n) this.ratingPulse = null;
            }, 280);
        },

        /** Mức đang hiển thị (hover ưu tiên hơn giá trị đã chọn) */
        displayRating() {
            return this.hoverRating || this.authorRating || 0;
        },

        /**
         * Trạng thái 1 sao: 'empty' | 'half' | 'full'
         * @param {number} starIndex 1–5
         */
        starFill(starIndex) {
            const value = this.displayRating();
            const fullAt = starIndex * 2;
            const halfAt = fullAt - 1;
            if (value >= fullAt) return 'full';
            if (value >= halfAt) return 'half';
            return 'empty';
        },

        toggleTag(id) {
            id = Number(id);
            if (this.selectedTags.includes(id)) {
                this.selectedTags = this.selectedTags.filter((t) => t !== id);
                return;
            }
            if (this.selectedCount >= 10) {
                this.clientError = 'Chỉ được chọn tối đa 10 thẻ.';
                return;
            }
            this.clientError = '';
            this.selectedTags = [...this.selectedTags, id];
        },

        addCustomTag() {
            const name = this.tagQuery.trim();
            if (!this.canCreateTag || !name) return;
            if (this.selectedCount >= 10) {
                this.clientError = 'Chỉ được chọn tối đa 10 thẻ.';
                return;
            }
            // Nếu trùng thẻ đã có → chọn thẻ đó
            const found = this.visibleTags.find((t) => t.name.toLowerCase() === name.toLowerCase());
            if (found) {
                this.toggleTag(found.id);
                this.tagQuery = '';
                return;
            }
            this.customTags = [...this.customTags, name];
            this.tagQuery = '';
            this.clientError = '';
        },

        removeCustomTag(name) {
            this.customTags = this.customTags.filter((t) => t !== name);
        },

        tagName(id) {
            const t = this.allTags.find((x) => Number(x.id) === Number(id));
            return t ? t.name : `#${id}`;
        },

        isPendingTag(id) {
            const t = this.allTags.find((x) => Number(x.id) === Number(id));
            return t && t.status === 'pending';
        },

        initMap() {
            if (this.mapReady || !window.google?.maps) return;
            const el = document.getElementById('experience-map');
            if (!el) return;

            const lat = parseFloat(this.latitude) || 16.0544;
            const lng = parseFloat(this.longitude) || 108.2022;
            const center = { lat, lng };

            this.map = new google.maps.Map(el, {
                center,
                zoom: 14,
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: false,
                zoomControl: true,
            });

            this.marker = new google.maps.Marker({
                position: center,
                map: this.map,
                draggable: true,
                title: 'Kéo để chỉnh vị trí',
            });

            this.marker.addListener('dragend', () => {
                const pos = this.marker.getPosition();
                this.latitude = String(pos.lat().toFixed(7));
                this.longitude = String(pos.lng().toFixed(7));
                this.mapHint = 'Đã cập nhật toạ độ.';
                this.placeId = '';
            });

            if (this.$refs.addressInput && google.maps.places) {
                this.autocomplete = new google.maps.places.Autocomplete(this.$refs.addressInput, {
                    fields: ['formatted_address', 'geometry', 'place_id', 'name'],
                    componentRestrictions: { country: ['vn'] },
                });
                this.autocomplete.bindTo('bounds', this.map);
                this.autocomplete.addListener('place_changed', () => {
                    const place = this.autocomplete.getPlace();
                    if (!place.geometry?.location) {
                        this.mapHint = 'Không tìm thấy toạ độ.';
                        return;
                    }
                    const loc = place.geometry.location;
                    this.latitude = String(loc.lat().toFixed(7));
                    this.longitude = String(loc.lng().toFixed(7));
                    this.address = place.formatted_address || this.address;
                    this.placeId = place.place_id || '';
                    if (place.name && !this.placeName) {
                        this.placeName = place.name;
                    }
                    this.map.setCenter(loc);
                    this.map.setZoom(15);
                    this.marker.setPosition(loc);
                    this.mapHint = 'Đã chọn địa điểm.';
                    this.locationError = '';
                });
            }

            this.mapReady = true;
        },

        syncMarkerFromInputs() {
            const lat = parseFloat(this.latitude);
            const lng = parseFloat(this.longitude);
            if (!this.marker || Number.isNaN(lat) || Number.isNaN(lng)) return;
            const pos = { lat, lng };
            this.marker.setPosition(pos);
            this.map?.panTo(pos);
        },

        useMyLocation() {
            this.locationError = '';
            if (!navigator.geolocation) {
                this.locationError = 'Trình duyệt không hỗ trợ định vị.';
                return;
            }
            this.locating = true;
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    this.latitude = String(pos.coords.latitude.toFixed(7));
                    this.longitude = String(pos.coords.longitude.toFixed(7));
                    this.placeId = '';
                    this.syncMarkerFromInputs();
                    if (this.map) this.map.setZoom(15);
                    this.mapHint = 'Đã dùng vị trí hiện tại.';
                    this.locating = false;
                },
                () => {
                    this.locationError = 'Không lấy được vị trí.';
                    this.locating = false;
                },
                { enableHighAccuracy: true, timeout: 12000 },
            );
        },

        onImagesChange(event) {
            if (!this.enableImages) return;
            const incoming = Array.from(event.target.files || []);
            const total = this.files.length + incoming.length;
            this.setFiles([...this.files, ...incoming].slice(0, 10));
            if (total > 10) this.clientError = 'Tối đa 10 ảnh.';
        },

        setFiles(files) {
            this.previews.forEach((p) => URL.revokeObjectURL(p.url));
            this.files = files;
            this.previews = files.map((file) => ({
                name: file.name,
                url: URL.createObjectURL(file),
            }));
            if (this.coverIndex >= this.files.length) this.coverIndex = 0;
            this.syncFileInput();
        },

        removePreview(index) {
            const next = this.files.filter((_, i) => i !== index);
            if (this.coverIndex === index) this.coverIndex = 0;
            else if (this.coverIndex > index) this.coverIndex -= 1;
            this.setFiles(next);
        },

        syncFileInput() {
            const input = this.$refs.fileInput;
            if (!input || typeof DataTransfer === 'undefined') return;
            const dt = new DataTransfer();
            this.files.forEach((f) => dt.items.add(f));
            input.files = dt.files;
        },

        onSubmit(event) {
            this.clientError = '';
            if (this.status === 'published') {
                const lat = parseFloat(this.latitude);
                const lng = parseFloat(this.longitude);
                if (Number.isNaN(lat) || Number.isNaN(lng)) {
                    event.preventDefault();
                    this.clientError = 'Công khai cần toạ độ — chọn địa điểm trên bản đồ.';
                    return;
                }
            }
            if (!this.title?.trim()) {
                event.preventDefault();
                this.clientError = 'Vui lòng nhập tiêu đề.';
                return;
            }
            if (!this.categoryId) {
                event.preventDefault();
                this.clientError = 'Vui lòng chọn danh mục.';
                return;
            }
            this.submitting = true;
        },
    }));
});
</script>
