'use client';

import MasterDataView from '@/components/master/MasterDataView';

export default function MasterTipeOsPage() {
  return (
    <MasterDataView
      title="Master Tipe OS"
      itemLabel="Tipe OS"
      endpoint="/master/tipe-os"
    />
  );
}
