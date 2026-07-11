import {
  Button,
  Form,
  Input,
  Modal,
  Popconfirm,
  Select,
  Space,
  Table,
  Tag as AntTag,
  message,
} from 'antd';
import { useQuery, useQueryClient } from '@tanstack/react-query';
import {
  createTag,
  deleteTag,
  listCategories,
  listTags,
  updateTag,
  updateTagStatus,
  type Tag,
} from '../api/resources';
import { useMemo, useState } from 'react';
import ListTotalFooter from '../components/ListTotalFooter';
import { resolveListTotal, sttIdColumn } from '../utils/listTable';

type StatusFilter = 'all' | 'pending' | 'approved';

export default function TagsPage() {
  const qc = useQueryClient();
  const [open, setOpen] = useState(false);
  const [editing, setEditing] = useState<Tag | null>(null);
  const [status, setStatus] = useState<StatusFilter>('all');
  const [page, setPage] = useState(1);
  const [statusLoadingId, setStatusLoadingId] = useState<number | null>(null);
  const [form] = Form.useForm();

  const { data, isLoading } = useQuery({
    queryKey: ['admin-tags', status, page],
    queryFn: () =>
      listTags({
        ...(status === 'all' ? {} : { status }),
        page,
      }),
  });

  const { data: categories } = useQuery({
    queryKey: ['admin-categories'],
    queryFn: listCategories,
  });

  const categoryOptions = useMemo(
    () => [
      { value: null as number | null, label: '— Toàn cục —' },
      ...(categories ?? []).map((c) => ({
        value: c.id,
        label: `${c.icon ? `${c.icon} ` : ''}${c.name}`,
      })),
    ],
    [categories],
  );

  const pendingCount = useMemo(
    () => (data?.data ?? []).filter((t) => t.status === 'pending').length,
    [data],
  );

  const invalidate = () => qc.invalidateQueries({ queryKey: ['admin-tags'] });

  const closeModal = () => {
    setOpen(false);
    setEditing(null);
    form.resetFields();
  };

  const openCreate = () => {
    setEditing(null);
    form.resetFields();
    form.setFieldsValue({ status: 'approved', category_id: null });
    setOpen(true);
  };

  const openEdit = (row: Tag) => {
    setEditing(row);
    form.setFieldsValue({
      name: row.name,
      slug: row.slug,
      category_id: row.category_id ?? null,
      status: row.status ?? 'approved',
    });
    setOpen(true);
  };

  const changeStatus = async (row: Tag, next: 'pending' | 'approved') => {
    if (row.status === next) return;
    setStatusLoadingId(row.id);
    try {
      await updateTagStatus(row.id, next);
      message.success(next === 'approved' ? 'Đã duyệt thẻ' : 'Đã chuyển chờ duyệt');
      invalidate();
    } catch {
      message.error('Không cập nhật được trạng thái');
    } finally {
      setStatusLoadingId(null);
    }
  };

  return (
    <>
      <div style={{ display: 'flex', gap: 12, marginBottom: 16, flexWrap: 'wrap', alignItems: 'center' }}>
        <Button type="primary" onClick={openCreate}>
          Thêm thẻ
        </Button>
        <Select
          value={status}
          style={{ width: 180 }}
          onChange={(v) => {
            setStatus(v);
            setPage(1);
          }}
          options={[
            { value: 'all', label: 'Tất cả trạng thái' },
            { value: 'pending', label: 'Chờ duyệt' },
            { value: 'approved', label: 'Đã duyệt' },
          ]}
        />
        {status === 'all' && pendingCount > 0 && (
          <AntTag color="orange">{pendingCount} thẻ chờ duyệt (trang này)</AntTag>
        )}
      </div>

      <Table
        rowKey="id"
        loading={isLoading}
        dataSource={data?.data}
        pagination={{
          current: data?.meta.current_page,
          total: data?.meta.total,
          pageSize: data?.meta.per_page,
          onChange: setPage,
        }}
        columns={[
          sttIdColumn<Tag>(data?.meta),
          { title: 'Tên', dataIndex: 'name' },
          { title: 'Slug', dataIndex: 'slug', ellipsis: true },
          {
            title: 'Danh mục',
            dataIndex: 'category_id',
            render: (_: unknown, row: Tag) =>
              row.category ? `${row.category.icon ?? ''} ${row.category.name}`.trim() : 'Toàn cục',
          },
          {
            title: 'Trạng thái',
            dataIndex: 'status',
            width: 160,
            render: (s: string | undefined, row: Tag) => (
              <Select
                size="small"
                value={s === 'pending' ? 'pending' : 'approved'}
                style={{ width: 132 }}
                loading={statusLoadingId === row.id}
                onChange={(v) => changeStatus(row, v as 'pending' | 'approved')}
                options={[
                  { value: 'pending', label: 'Chờ duyệt' },
                  { value: 'approved', label: 'Đã duyệt' },
                ]}
              />
            ),
          },
          { title: 'Usage', dataIndex: 'usage_count', width: 80 },
          {
            title: 'Tạo bởi',
            width: 120,
            render: (_: unknown, row: Tag) =>
              row.creator?.name ?? (row.created_by ? `User #${row.created_by}` : 'Admin'),
          },
          {
            title: 'Thao tác',
            width: 200,
            render: (_: unknown, row: Tag) => (
              <Space size="small" wrap>
                <Button size="small" onClick={() => openEdit(row)}>
                  Sửa
                </Button>
                {row.status === 'pending' && (
                  <Button size="small" type="primary" onClick={() => changeStatus(row, 'approved')}>
                    Duyệt
                  </Button>
                )}
                <Popconfirm
                  title="Xoá thẻ này?"
                  description="Thẻ sẽ gỡ khỏi các trải nghiệm đã gắn."
                  okText="Xoá"
                  cancelText="Huỷ"
                  okButtonProps={{ danger: true }}
                  onConfirm={async () => {
                    try {
                      await deleteTag(row.id);
                      message.success('Đã xoá');
                      invalidate();
                    } catch {
                      message.error('Không xoá được thẻ');
                    }
                  }}
                >
                  <Button size="small" danger>
                    Xoá
                  </Button>
                </Popconfirm>
              </Space>
            ),
          },
        ]}
      />
      <ListTotalFooter
        total={resolveListTotal(data?.meta.total, data?.data?.length)}
        loading={isLoading}
      />

      <Modal
        title={editing ? 'Sửa thẻ' : 'Thêm thẻ'}
        open={open}
        onCancel={closeModal}
        onOk={() => form.submit()}
        destroyOnClose
      >
        <Form
          form={form}
          layout="vertical"
          onFinish={async (values) => {
            const payload = {
              name: values.name as string,
              slug: (values.slug as string) || undefined,
              category_id: values.category_id ?? null,
              status: values.status as 'pending' | 'approved',
            };
            try {
              if (editing) {
                await updateTag(editing.id, payload);
                message.success('Đã cập nhật thẻ');
              } else {
                await createTag(payload);
                message.success('Đã tạo thẻ');
              }
              closeModal();
              invalidate();
            } catch {
              message.error(editing ? 'Không cập nhật được' : 'Không tạo được thẻ');
            }
          }}
        >
          <Form.Item name="name" label="Tên" rules={[{ required: true, message: 'Nhập tên thẻ' }]}>
            <Input placeholder="Ví dụ: View đẹp" />
          </Form.Item>
          <Form.Item name="slug" label="Slug" extra="Để trống khi tạo mới để tự sinh từ tên">
            <Input placeholder="view-dep" />
          </Form.Item>
          <Form.Item name="category_id" label="Danh mục">
            <Select
              allowClear
              placeholder="Toàn cục"
              options={categoryOptions}
              // null option uses value null
            />
          </Form.Item>
          <Form.Item name="status" label="Trạng thái" initialValue="approved">
            <Select
              options={[
                { value: 'pending', label: 'Chờ duyệt' },
                { value: 'approved', label: 'Đã duyệt' },
              ]}
            />
          </Form.Item>
        </Form>
      </Modal>
    </>
  );
}
