Description of External library imports into Moodle

Flexitour Instructions
----------------------
1. Clone https://github.com/andrewnicols/flexitour into an unrelated directory
2. Copy /build/tour.js to amd/src/tour.js
3. Open the amd/src/tour.js file and find the AMD module define.
4. Change the "popper" inclusion to "./popper"
5. Update thirdpartylibs.xml
6. Run `grunt amd`

Popper.js Instructions
----------------------
1. Clone https://github.com/FezVrasta/popper.js into an unrelated directory
2. Copy /build/popper.js to amd/src/popper.js
3. Update thirdpartylibs.xml
4. Run `grunt amd`

 Modifications:
 * tour.js - MDL-60207 User tour should be closed when user touch/click outside it
    These changes can be reverted once the following pull requests have been integrated upstream:
    https://github.com/andrewnicols/flexitour/pull/2/commits/4ca7fd82b32c38662fd93dca798c018cdfbf041a
