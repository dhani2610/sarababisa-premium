'use client';

import MasterDataView from '@/components/master/MasterDataView';

export default function MasterMetodePembayaranPage() {
  return (
    <MasterDataView
      title="Master Metode Pembayaran"
      itemLabel="Metode Pembayaran"
      endpoint="/master/metode-pembayaran"
    />
  );
}
