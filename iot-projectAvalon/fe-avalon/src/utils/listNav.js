const listNav = [
  { name: "Beranda", url: "/beranda" },
  {
    name: "Aktivitas Kami",
    url: "/aktivitas",
    children: [
      { name: "Innovillage 2023", url: "/aktivitas/innovillage-2023" },
      { name: "Inkubasi BTP", url: "/aktivitas/inkubasi-btp" },
    ],
  },
  { name: "Kontak Kami", url: "/kontak-kami" },
];

export default listNav;
