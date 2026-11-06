# Room Photography

The demo uses room photographs from the Nghe An listings on
[Phongtro123](https://phongtro123.com/tinh-thanh/nghe-an).
The original source URLs are listed in `database/seeders/HouseRoomSeeder.php`.
Local copies in `public/images/rooms` retain the source filenames. The shared
room image component uses these copies when the matching remote URL is present.

These photographs belong to their respective listing authors and are used as
demo content for this graduation project. Replace them with photographs of the
actual branches before publishing a real rental business.

The homepage banner uses a separate illustrative interior photograph from
[Unsplash](https://images.unsplash.com/photo-1522708323590-d24dbb6b0267).
Local versions at `public/images/home/living-space.jpg` (2200px) and
`public/images/home/living-space-small.jpg` (800px) provide responsive image
sources. This banner does not depict a particular room or branch in Vinh;
the room listings continue to use their own room photographs.

The favicon uses the [Font Awesome 5 home icon](https://github.com/FortAwesome/Font-Awesome/blob/5.x/svgs/solid/home.svg),
licensed under CC BY 4.0. Its fill color matches the website brand.
