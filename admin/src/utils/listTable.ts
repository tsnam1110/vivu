import type { ColumnsType, TablePaginationConfig } from 'antd/es/table';

/** Meta phân trang từ API (nếu có). */
export type PaginationMeta = {
  current_page?: number;
  per_page?: number;
  total?: number;
};

/**
 * STT liên tục theo bộ lọc + trang hiện tại (1-based).
 * Trang 2, per_page 15, index 0 → 16.
 */
export function computeStt(rowIndex: number, meta?: PaginationMeta | null): number {
  const page = meta?.current_page ?? 1;
  const perPage = meta?.per_page ?? 0;
  const offset = perPage > 0 ? (page - 1) * perPage : 0;
  return offset + rowIndex + 1;
}

/** Quy tắc hiển thị STT-ID: `{stt}-{id}` (vd: 1-42). */
export function formatSttId(stt: number, id: number | string): string {
  return `${stt}-${id}`;
}

/**
 * Cột Ant Design «STT» theo quy tắc stt-id.
 * Thay cột ID thô trên các trang danh sách.
 */
export function sttIdColumn<T extends { id: number | string }>(
  meta?: PaginationMeta | null,
): ColumnsType<T>[number] {
  return {
    title: 'STT',
    key: 'stt_id',
    width: 96,
    render: (_: unknown, record: T, index: number) =>
      formatSttId(computeStt(index, meta), record.id),
  };
}

/** Tổng ngắn gọn cùng dòng phân trang: «12 bản ghi». */
export function paginationShowTotal(total: number): string {
  return `${total.toLocaleString('vi-VN')} bản ghi`;
}

/** Phân trang server (meta API) + showTotal. */
export function serverPagination(
  meta: PaginationMeta | undefined | null,
  onChange: (page: number) => void,
  extras?: Partial<TablePaginationConfig>,
): TablePaginationConfig {
  return {
    current: meta?.current_page,
    total: meta?.total,
    pageSize: meta?.per_page,
    onChange,
    showSizeChanger: false,
    showTotal: paginationShowTotal,
    ...extras,
  };
}

/** Phân trang client (catalog) + showTotal luôn hiện. */
export function clientPagination(
  extras?: Partial<TablePaginationConfig>,
): TablePaginationConfig {
  return {
    showSizeChanger: false,
    showTotal: paginationShowTotal,
    hideOnSinglePage: false,
    ...extras,
  };
}
