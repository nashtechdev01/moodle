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
    * tour.js - MDL-60078 User tours accessibility: Tabbing should go back into the pop-up
      These changes can be reverted once the following pull requests have been integrated upstream:
      https://github.com/andrewnicols/flexitour/pull/6/commits/e1ac79b269bbfe196a16cefe70b50c18175b050b
