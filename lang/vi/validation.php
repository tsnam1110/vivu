<?php

return [
    'accepted' => 'Trường :attribute phải được chấp nhận.',
    'required' => 'Trường :attribute là bắt buộc.',
    'email' => 'Trường :attribute phải là email hợp lệ.',
    'unique' => 'Trường :attribute đã được sử dụng.',
    'confirmed' => 'Xác nhận :attribute không khớp.',
    'exists' => 'Giá trị :attribute không hợp lệ.',
    'max' => [
        'string' => 'Trường :attribute không được vượt quá :max ký tự.',
        'file' => 'Trường :attribute không được vượt quá :max KB.',
        'array' => 'Trường :attribute không được có quá :max phần tử.',
    ],
    'min' => [
        'string' => 'Trường :attribute phải có ít nhất :min ký tự.',
    ],
    'between' => [
        'numeric' => 'Trường :attribute phải nằm trong khoảng :min đến :max.',
        'integer' => 'Trường :attribute phải nằm trong khoảng :min đến :max.',
    ],
    'integer' => 'Trường :attribute phải là số nguyên.',
    'numeric' => 'Trường :attribute phải là số.',
    'image' => 'Trường :attribute phải là ảnh.',
    'array' => 'Trường :attribute phải là mảng.',
    'string' => 'Trường :attribute phải là chuỗi.',
    'boolean' => 'Trường :attribute phải là true hoặc false.',
    'published_requires_coordinates' => 'Trải nghiệm công khai bắt buộc có toạ độ (latitude, longitude).',
    'comment_one_level' => 'Chỉ được trả lời bình luận gốc (1 cấp).',
    'category_has_experiences' => 'Không thể xoá danh mục còn trải nghiệm. Hãy vô hiệu hoá thay vì xoá.',
    'invalid_traits' => 'Nhãn không hợp lệ: :traits',
    'attributes' => [
        'title' => 'tiêu đề',
        'email' => 'email',
        'password' => 'mật khẩu',
        'username' => 'tên người dùng',
        'name' => 'tên',
        'latitude' => 'vĩ độ',
        'longitude' => 'kinh độ',
        'body' => 'nội dung',
        'rating' => 'đánh giá',
        'category_id' => 'danh mục',
    ],
];
