# WTE-S3-03 — Bump ruleset_version + contract test

**Plan:** [`../../features/what-to-eat-next-plan.md`](../../features/what-to-eat-next-plan.md)  
**Sóng:** S3 · **Owner:** BE/Docs  
**Phụ thuộc:** S3-01 và/hoặc S3-02 đã merge (hoặc bump patch nếu chỉ S3-01)

---

## Nhiệm vụ

Đồng bộ `ruleset_version` toàn hệ + test assert `meta.ruleset_version`.

## Đọc trước

- `config/what_to_eat.php`
- `docs/features/what-to-eat-ruleset.md` changelog
- `WhatToEatController` meta
- `manifest.json` `ruleset_min`

## Việc cần làm

1. Chốt semver (vd `0.3.0` nếu feast+diversity; `0.2.1` nếu chỉ soft diversity).
2. Cập nhật config, docs, manifest `ruleset_min`.
3. Feature test assert version.
4. Đánh WTE-S3-03 done.

## DoD

- [ ] Một version duy nhất code+doc
- [ ] Test assert version
