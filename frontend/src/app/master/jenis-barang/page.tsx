'use client';

import MasterDataView from '@/components/master/MasterDataView';

export default function MasterJenisBarangPage() {
  return (
    <MasterDataView
      title="Master Jenis Barang"
      itemLabel="Jenis Barang"
      endpoint="/master/jenis-barang"
    />
  );
}
