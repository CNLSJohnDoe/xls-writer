<?php

namespace Xls\Writer;

/**
 * Class for generating Excel XF records (formats)
 *
 * @author   Xavier Noguer <xnoguer@rezebra.com>
 * @category FileFormats
 * @package  Spreadsheet_Excel_Writer
 */

class Format
{
    const BORDER_THIN = 1;
    const BORDER_THICK = 2;

    const UNDERLINE_ONCE = 1;
    const UNDERLINE_TWICE = 2;

    const SCRIPT_SUPER = 1;
    const SCRIPT_SUB = 2;

    /**
     * @var integer
     */
    protected $biffVersion;

    /**
     * The index given by the workbook when creating a new format.
     * @var integer
     */
    public $xfIndex;

    /**
     * Index to the FONT record.
     * @var integer
     */
    public $fontIndex;

    /**
     * The font name (ASCII).
     * @var string
     */
    public $fontName;

    /**
     * Height of font (1/20 of a point)
     * @var integer
     */
    public $size;

    /**
     * Bold style
     * @var integer
     */
    public $bold;

    /**
     * Bit specifiying if the font is italic.
     * @var integer
     */
    public $italic;

    /**
     * Index to the cell's color
     * @var integer
     */
    public $color;

    /**
     * The text underline property
     * @var integer
     */
    public $underline;

    /**
     * Bit specifiying if the font has strikeout.
     * @var integer
     */
    public $fontStrikeout;

    /**
     * Bit specifiying if the font has outline.
     * @var integer
     */
    public $fontOutline;

    /**
     * Bit specifiying if the font has shadow.
     * @var integer
     */
    public $fontShadow;

    /**
     * 2 bytes specifiying the script type for the font.
     * @var integer
     */
    public $fontScript;

    /**
     * Byte specifiying the font family.
     * @var integer
     */
    public $fontFamily;

    /**
     * Byte specifiying the font charset.
     * @var integer
     */
    public $fontCharset;

    /**
     * An index (2 bytes) to a FORMAT record (number format).
     * @var integer
     */
    public $numFormat;

    /**
     * Bit specifying if formulas are hidden.
     * @var integer
     */
    public $hidden;

    /**
     * Bit specifying if the cell is locked.
     * @var integer
     */
    public $locked;

    /**
     * The three bits specifying the text horizontal alignment.
     * @var integer
     */
    public $textHorAlign;

    /**
     * Bit specifying if the text is wrapped at the right border.
     * @var integer
     */
    public $textWrap;

    /**
     * The three bits specifying the text vertical alignment.
     * @var integer
     */
    public $textVertAlign;

    /**
     * 1 bit, apparently not used.
     * @var integer
     */
    public $textJustlast;

    /**
     * The two bits specifying the text rotation.
     * @var integer
     */
    public $rotation;

    /**
     * The cell's foreground color.
     * @var integer
     */
    public $fgColor;

    /**
     * The cell's background color.
     * @var integer
     */
    public $bgColor;

    /**
     * The cell's background fill pattern.
     * @var integer
     */
    public $pattern;

    /**
     * Style of the bottom border of the cell
     * @var integer
     */
    public $bottom;

    /**
     * Color of the bottom border of the cell.
     * @var integer
     */
    public $bottomColor;

    /**
     * Style of the top border of the cell
     * @var integer
     */
    public $top;

    /**
     * Color of the top border of the cell.
     * @var integer
     */
    public $topColor;

    /**
     * Style of the left border of the cell
     * @var integer
     */
    public $left;

    /**
     * Color of the left border of the cell.
     * @var integer
     */
    public $leftColor;

    /**
     * Style of the right border of the cell
     * @var integer
     */
    public $right;

    /**
     * Color of the right border of the cell.
     * @var integer
     */
    public $rightColor;

    /**
     * Constructor
     *
     * @param integer $biffVersion
     * @param integer $index the XF index for the format.
     * @param array $properties array with properties to be set on initialization.
     */
    public function __construct($biffVersion, $index = 0, $properties = array())
    {
        $this->xfIndex = $index;
        $this->biffVersion = $biffVersion;
        $this->fontIndex = 0;
        $this->fontName = 'Arial';
        $this->size = 10;
        $this->bold = 0x0190;
        $this->italic = 0;
        $this->color = 0x7FFF;
        $this->underline = 0;
        $this->fontStrikeout = 0;
        $this->fontOutline = 0;
        $this->fontShadow = 0;
        $this->fontScript = 0;
        $this->fontFamily = 0;
        $this->fontCharset = 0;

        $this->numFormat = 0;

        $this->hidden = 0;
        $this->locked = 0;

        $this->textHorAlign = 0;
        $this->textWrap = 0;
        $this->textVertAlign = 2;
        $this->textJustlast = 0;
        $this->rotation = 0;

        $this->fgColor = 0x40;
        $this->bgColor = 0x41;

        $this->pattern = 0;

        $this->bottom = 0;
        $this->top = 0;
        $this->left = 0;
        $this->right = 0;
        $this->diag = 0;

        $this->bottomColor = 0x40;
        $this->topColor = 0x40;
        $this->leftColor = 0x40;
        $this->rightColor = 0x40;
        $this->diag_color = 0x40;

        // Set properties passed to Workbook::addFormat()
        foreach ($properties as $property => $value) {
            if (method_exists($this, 'set' . ucwords($property))) {
                $methodName = 'set' . ucwords($property);
                $this->$methodName($value);
            }
        }
    }


    /**
     * Generate an Excel BIFF XF record (style or cell).
     *
     * @param string $style The type of the XF record ('style' or 'cell').
     * @return string The XF record
     */
    public function getXf($style)
    {
        // Set the type of the XF record and some of the attributes.
        if ($style == 'style') {
            $style = 0xFFF5;
        } else {
            $style = $this->locked;
            $style |= $this->hidden << 1;
        }

        // Flags to indicate if attributes have been set.
        $atrNum = ($this->numFormat != 0) ? 1 : 0;
        $atrFnt = ($this->fontIndex != 0) ? 1 : 0;
        $atrAlc = ($this->textWrap) ? 1 : 0;
        $atrBdr = ($this->bottom
            || $this->top
            || $this->left
            || $this->right) ? 1 : 0;
        $atrPat = (($this->fgColor != 0x40)
            || ($this->bgColor != 0x41)
            || $this->pattern) ? 1 : 0;
        $atrProt = $this->locked | $this->hidden;

        // Zero the default border colour if the border has not been set.
        if ($this->bottom == 0) {
            $this->bottomColor = 0;
        }
        if ($this->top == 0) {
            $this->topColor = 0;
        }
        if ($this->right == 0) {
            $this->rightColor = 0;
        }
        if ($this->left == 0) {
            $this->leftColor = 0;
        }
        if ($this->diag == 0) {
            $this->diag_color = 0;
        }

        $record = 0x00E0; // Record identifier
        if ($this->biffVersion === BIFFwriter::VERSION_5) {
            $length = 0x0010; // Number of bytes to follow
        } else {
            $length = 0x0014;
        }

        $ifnt = $this->fontIndex; // Index to FONT record
        $ifmt = $this->numFormat; // Index to FORMAT record
        if ($this->biffVersion === BIFFwriter::VERSION_5) {
            $align = $this->textHorAlign; // Alignment
            $align |= $this->textWrap << 3;
            $align |= $this->textVertAlign << 4;
            $align |= $this->textJustlast << 7;
            $align |= $this->rotation << 8;
            $align |= $atrNum << 10;
            $align |= $atrFnt << 11;
            $align |= $atrAlc << 12;
            $align |= $atrBdr << 13;
            $align |= $atrPat << 14;
            $align |= $atrProt << 15;

            $icv = $this->fgColor; // fg and bg pattern colors
            $icv |= $this->bgColor << 7;

            $fill = $this->pattern; // Fill and border line style
            $fill |= $this->bottom << 6;
            $fill |= $this->bottomColor << 9;

            $border1 = $this->top; // Border line style and color
            $border1 |= $this->left << 3;
            $border1 |= $this->right << 6;
            $border1 |= $this->topColor << 9;

            $border2 = $this->leftColor; // Border color
            $border2 |= $this->rightColor << 7;

            $header = pack("vv", $record, $length);
            $data = pack(
                "vvvvvvvv",
                $ifnt,
                $ifmt,
                $style,
                $align,
                $icv,
                $fill,
                $border1,
                $border2
            );
        } else {
            $align = $this->textHorAlign; // Alignment
            $align |= $this->textWrap << 3;
            $align |= $this->textVertAlign << 4;
            $align |= $this->textJustlast << 7;

            $usedAttr = $atrNum << 2;
            $usedAttr |= $atrFnt << 3;
            $usedAttr |= $atrAlc << 4;
            $usedAttr |= $atrBdr << 5;
            $usedAttr |= $atrPat << 6;
            $usedAttr |= $atrProt << 7;

            $icv = $this->fgColor; // fg and bg pattern colors
            $icv |= $this->bgColor << 7;

            $border1 = $this->left; // Border line style and color
            $border1 |= $this->right << 4;
            $border1 |= $this->top << 8;
            $border1 |= $this->bottom << 12;
            $border1 |= $this->leftColor << 16;
            $border1 |= $this->rightColor << 23;
            $diagTlToRb = 0;
            $diagTrToLb = 0;
            $border1 |= $diagTlToRb << 30;
            $border1 |= $diagTrToLb << 31;

            $border2 = $this->topColor; // Border color
            $border2 |= $this->bottomColor << 7;
            $border2 |= $this->diag_color << 14;
            $border2 |= $this->diag << 21;
            $border2 |= $this->pattern << 26;

            $header = pack("vv", $record, $length);

            $rotation = $this->rotation;
            $biff8Options = 0x00;
            $data = pack("vvvC", $ifnt, $ifmt, $style, $align);
            $data .= pack("CCC", $rotation, $biff8Options, $usedAttr);
            $data .= pack("VVv", $border1, $border2, $icv);
        }

        return ($header . $data);
    }

    /**
     * Generate an Excel BIFF FONT record.
     *
     * @return string The FONT record
     */
    public function getFont()
    {
        $dyHeight = $this->size * 20; // Height of font (1/20 of a point)
        $icv = $this->color; // Index to color palette
        $bls = $this->bold; // Bold style
        $sss = $this->fontScript; // Superscript/subscript
        $uls = $this->underline; // Underline
        $bFamily = $this->fontFamily; // Font family
        $bCharSet = $this->fontCharset; // Character set
        $encoding = 0;

        $cch = strlen($this->fontName); // Length of font name
        $record = 0x31; // Record identifier
        if ($this->biffVersion === BIFFwriter::VERSION_5) {
            $length = 0x0F + $cch; // Record length
        } else {
            $length = 0x10 + $cch;
        }

        $reserved = 0x00; // Reserved
        $grbit = 0x00; // Font attributes
        if ($this->italic) {
            $grbit |= 0x02;
        }
        if ($this->fontStrikeout) {
            $grbit |= 0x08;
        }
        if ($this->fontOutline) {
            $grbit |= 0x10;
        }
        if ($this->fontShadow) {
            $grbit |= 0x20;
        }

        $header = pack("vv", $record, $length);
        if ($this->biffVersion === BIFFwriter::VERSION_5) {
            $data = pack(
                "vvvvvCCCCC",
                $dyHeight,
                $grbit,
                $icv,
                $bls,
                $sss,
                $uls,
                $bFamily,
                $bCharSet,
                $reserved,
                $cch
            );
        } else {
            $data = pack(
                "vvvvvCCCCCC",
                $dyHeight,
                $grbit,
                $icv,
                $bls,
                $sss,
                $uls,
                $bFamily,
                $bCharSet,
                $reserved,
                $cch,
                $encoding
            );
        }

        return ($header . $data . $this->fontName);
    }

    /**
     * Returns a unique hash key for a font.
     * Used by Workbook::storeAllFonts()
     *
     * The elements that form the key are arranged to increase the probability of
     * generating a unique key. Elements that hold a large range of numbers
     * (eg. _color) are placed between two binary elements such as _italic
     *
     * @return string A key for this font
     */
    public function getFontKey()
    {
        $key = "$this->fontName$this->size";
        $key .= "$this->fontScript$this->underline";
        $key .= "$this->fontStrikeout$this->bold$this->fontOutline";
        $key .= "$this->fontFamily$this->fontCharset";
        $key .= "$this->fontShadow$this->color$this->italic";
        $key = str_replace(' ', '_', $key);

        return ($key);
    }

    /**
     * Returns the index used by Worksheet::xf()
     *
     * @return integer The index for the XF record
     */
    public function getXfIndex()
    {
        return $this->xfIndex;
    }

    /**
     * Used in conjunction with the set_xxx_color methods to convert a color
     * string into a number. Color range is 0..63 but we will restrict it
     * to 8..63 to comply with Gnumeric. Colors 0..7 are repeated in 8..15.
     *
     * @param string $name name of the color (i.e.: 'blue', 'red', etc..). Optional.
     *
     * @return integer The color index
     */
    protected function getColor($name = '')
    {
        $colors = array(
            'aqua' => 0x07,
            'cyan' => 0x07,
            'black' => 0x00,
            'blue' => 0x04,
            'brown' => 0x10,
            'magenta' => 0x06,
            'fuchsia' => 0x06,
            'gray' => 0x17,
            'grey' => 0x17,
            'green' => 0x11,
            'lime' => 0x03,
            'navy' => 0x12,
            'orange' => 0x35,
            'purple' => 0x14,
            'red' => 0x02,
            'silver' => 0x16,
            'white' => 0x01,
            'yellow' => 0x05
        );

        // Return the default color, 0x7FFF, if undef,
        if ($name === '') {
            return (0x7FFF);
        }

        // or the color string converted to an integer,
        if (isset($colors[$name])) {
            return ($colors[$name]);
        }

        // or the default color if string is unrecognised,
        if (preg_match("/\D/", $name)) {
            return (0x7FFF);
        }

        // or the default color if arg is outside range,
        if ($name > 63) {
            return (0x7FFF);
        }

        // or an integer in the valid range
        return $name;
    }

    /**
     * Set cell alignment.
     *
     * @param string $location alignment for the cell ('left', 'right', etc...).
     */
    public function setAlign($location)
    {
        if (preg_match("/\d/", $location)) {
            return; // Ignore numbers
        }

        $location = strtolower($location);

        if ($location == 'left') {
            $this->textHorAlign = 1;
        }
        if ($location == 'centre') {
            $this->textHorAlign = 2;
        }
        if ($location == 'center') {
            $this->textHorAlign = 2;
        }
        if ($location == 'right') {
            $this->textHorAlign = 3;
        }
        if ($location == 'fill') {
            $this->textHorAlign = 4;
        }
        if ($location == 'justify') {
            $this->textHorAlign = 5;
        }
        if ($location == 'merge') {
            $this->textHorAlign = 6;
        }
        if ($location == 'equal_space') { // For T.K.
            $this->textHorAlign = 7;
        }
        if ($location == 'top') {
            $this->textVertAlign = 0;
        }
        if ($location == 'vcentre') {
            $this->textVertAlign = 1;
        }
        if ($location == 'vcenter') {
            $this->textVertAlign = 1;
        }
        if ($location == 'bottom') {
            $this->textVertAlign = 2;
        }
        if ($location == 'vjustify') {
            $this->textVertAlign = 3;
        }
        if ($location == 'vequal_space') { // For T.K.
            $this->textVertAlign = 4;
        }
    }

    /**
     * Set cell horizontal alignment.
     *
     * @param string $location alignment for the cell ('left', 'right', etc...).
     */
    public function setHAlign($location)
    {
        if (preg_match("/\d/", $location)) {
            return; // Ignore numbers
        }

        $location = strtolower($location);

        if ($location == 'left') {
            $this->textHorAlign = 1;
        }
        if ($location == 'centre') {
            $this->textHorAlign = 2;
        }
        if ($location == 'center') {
            $this->textHorAlign = 2;
        }
        if ($location == 'right') {
            $this->textHorAlign = 3;
        }
        if ($location == 'fill') {
            $this->textHorAlign = 4;
        }
        if ($location == 'justify') {
            $this->textHorAlign = 5;
        }
        if ($location == 'merge') {
            $this->textHorAlign = 6;
        }
        if ($location == 'equal_space') { // For T.K.
            $this->textHorAlign = 7;
        }
    }

    /**
     * Set cell vertical alignment.
     *
     * @param string $location alignment for the cell ('top', 'vleft', 'vright', etc...).
     */
    public function setVAlign($location)
    {
        if (preg_match("/\d/", $location)) {
            return; // Ignore numbers
        }

        $location = strtolower($location);

        if ($location == 'top') {
            $this->textVertAlign = 0;
        }
        if ($location == 'vcentre') {
            $this->textVertAlign = 1;
        }
        if ($location == 'vcenter') {
            $this->textVertAlign = 1;
        }
        if ($location == 'bottom') {
            $this->textVertAlign = 2;
        }
        if ($location == 'vjustify') {
            $this->textVertAlign = 3;
        }
        if ($location == 'vequal_space') { // For T.K.
            $this->textVertAlign = 4;
        }
    }

    /**
     * This is an alias for the unintuitive setAlign('merge')
     *
     */
    public function setMerge()
    {
        $this->setAlign('merge');
    }

    /**
     * Sets the boldness of the text.
     * Bold has a range 100..1000.
     * 0 (400) is normal. 1 (700) is bold.
     *
     * @param integer $weight Weight for the text, 0 maps to 400 (normal text),
    1 maps to 700 (bold text). Valid range is: 100-1000.
    It's Optional, default is 1 (bold).
     */
    public function setBold($weight = 1)
    {
        if ($weight == 1) {
            $weight = 0x2BC; // Bold text
        }
        if ($weight == 0) {
            $weight = 0x190; // Normal text
        }
        if ($weight < 0x064) {
            $weight = 0x190; // Lower bound
        }
        if ($weight > 0x3E8) {
            $weight = 0x190; // Upper bound
        }
        $this->bold = $weight;
    }


    /************************************
     * FUNCTIONS FOR SETTING CELLS BORDERS
     */

    /**
     * Sets the width for the bottom border of the cell
     *
     * @param integer $style style of the cell border (BORDER_THIN or BORDER_THICK).
     */
    public function setBottom($style)
    {
        $this->bottom = $style;
    }

    /**
     * Sets the width for the top border of the cell
     *
     * @param integer $style style of the cell top border (BORDER_THIN or BORDER_THICK).
     */
    public function setTop($style)
    {
        $this->top = $style;
    }

    /**
     * Sets the width for the left border of the cell
     *
     * @param integer $style style of the cell left border (BORDER_THIN or BORDER_THICK).
     */
    public function setLeft($style)
    {
        $this->left = $style;
    }

    /**
     * Sets the width for the right border of the cell
     *
     * @param integer $style style of the cell right border (BORDER_THIN or BORDER_THICK).
     */
    public function setRight($style)
    {
        $this->right = $style;
    }


    /**
     * Set cells borders to the same style
     *
     * @param integer $style style to apply for all cell borders (BORDER_THIN or BORDER_THICK).
     */
    public function setBorder($style)
    {
        $this->setBottom($style);
        $this->setTop($style);
        $this->setLeft($style);
        $this->setRight($style);
    }


    /*******************************************
     * FUNCTIONS FOR SETTING CELLS BORDERS COLORS
     */

    /**
     * Sets all the cell's borders to the same color
     *
     * @param mixed $color The color we are setting. Either a string (like 'blue'),
     *                     or an integer (range is [8...63]).
     */
    public function setBorderColor($color)
    {
        $this->setBottomColor($color);
        $this->setTopColor($color);
        $this->setLeftColor($color);
        $this->setRightColor($color);
    }

    /**
     * Sets the cell's bottom border color
     *
     * @param mixed $color either a string (like 'blue'), or an integer (range is [8...63]).
     */
    public function setBottomColor($color)
    {
        $value = $this->getColor($color);
        $this->bottomColor = $value;
    }

    /**
     * Sets the cell's top border color
     *
     * @param mixed $color either a string (like 'blue'), or an integer (range is [8...63]).
     */
    public function setTopColor($color)
    {
        $value = $this->getColor($color);
        $this->topColor = $value;
    }

    /**
     * Sets the cell's left border color
     *
     * @param mixed $color either a string (like 'blue'), or an integer (range is [8...63]).
     */
    public function setLeftColor($color)
    {
        $value = $this->getColor($color);
        $this->leftColor = $value;
    }

    /**
     * Sets the cell's right border color
     *
     * @param mixed $color either a string (like 'blue'), or an integer (range is [8...63]).
     */
    public function setRightColor($color)
    {
        $value = $this->getColor($color);
        $this->rightColor = $value;
    }


    /**
     * Sets the cell's foreground color
     *
     * @param mixed $color either a string (like 'blue'), or an integer (range is [8...63]).
     */
    public function setFgColor($color)
    {
        $value = $this->getColor($color);
        $this->fgColor = $value;
        if ($this->pattern == 0) { // force color to be seen
            $this->pattern = 1;
        }
    }

    /**
     * Sets the cell's background color
     *
     * @param mixed $color either a string (like 'blue'), or an integer (range is [8...63]).
     */
    public function setBgColor($color)
    {
        $value = $this->getColor($color);
        $this->bgColor = $value;
        if ($this->pattern == 0) { // force color to be seen
            $this->pattern = 1;
        }
    }

    /**
     * Sets the cell's color
     *
     * @param mixed $color either a string (like 'blue'), or an integer (range is [8...63]).
     */
    public function setColor($color)
    {
        $value = $this->getColor($color);
        $this->color = $value;
    }

    /**
     * Sets the fill pattern attribute of a cell
     *
     * @param integer $arg Optional. Defaults to 1. Meaningful values are: 0-18,
     *                     0 meaning no background.
     */
    public function setPattern($arg = 1)
    {
        $this->pattern = $arg;
    }

    /**
     * Sets the underline of the text
     *
     * @param integer $underline The value for underline. Possible values are:
     * UNDERLINE_ONCE => underline, UNDERLINE_TWICE => double underline.
     */
    public function setUnderline($underline)
    {
        $this->underline = $underline;
    }

    /**
     * Sets the font style as italic
     *
     */
    public function setItalic()
    {
        $this->italic = 1;
    }

    /**
     * Sets the font size
     *
     * @param integer $size The font size (in pixels I think).
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * Sets text wrapping
     *
     */
    public function setTextWrap()
    {
        $this->textWrap = 1;
    }

    /**
     * Sets the orientation of the text
     *
     * @param integer $angle The rotation angle for the text (clockwise). Possible
    values are: 0, 90, 270 and -1 for stacking top-to-bottom.
     * @throws \Exception
     */
    public function setTextRotation($angle)
    {
        switch ($angle) {
            case 0:
                $this->rotation = 0;
                break;
            case 90:
                if ($this->biffVersion === BIFFwriter::VERSION_5) {
                    $this->rotation = 3;
                } else {
                    $this->rotation = 180;
                }
                break;
            case 270:
                if ($this->biffVersion == BIFFwriter::VERSION_5) {
                    $this->rotation = 2;
                } else {
                    $this->rotation = 90;
                }
                break;
            case -1:
                if ($this->biffVersion == BIFFwriter::VERSION_5) {
                    $this->rotation = 1;
                } else {
                    $this->rotation = 255;
                }
                break;
            default:
                throw new \Exception(
                    "Invalid value for angle." .
                    " Possible values are: 0, 90, 270 and -1 " .
                    "for stacking top-to-bottom."
                );
                break;
        }
    }

    /**
     * Sets the numeric format.
     * It can be date, time, currency, etc...
     *
     * @param integer $numFormat The numeric format.
     */
    public function setNumFormat($numFormat)
    {
        $this->numFormat = $numFormat;
    }

    /**
     * Sets font as strikeout.
     *
     */
    public function setStrikeOut()
    {
        $this->fontStrikeout = 1;
    }

    /**
     * Sets outlining for a font.
     *
     */
    public function setOutLine()
    {
        $this->fontOutline = 1;
    }

    /**
     * Sets font as shadow.
     *
     */
    public function setShadow()
    {
        $this->fontShadow = 1;
    }

    /**
     * Sets the script type of the text
     *
     * @param integer $script The value for script type. Possible values are:
     * SCRIPT_SUPER => superscript, SCRIPT_SUB => subscript.
     */
    public function setScript($script)
    {
        $this->fontScript = $script;
    }

    /**
     * Locks a cell.
     */
    public function setLocked()
    {
        $this->locked = 1;
    }

    /**
     * Unlocks a cell. Useful for unprotecting particular cells of a protected sheet.
     */
    public function setUnLocked()
    {
        $this->locked = 0;
    }

    /**
     * Sets the font family name.
     *
     * @param string $fontFamily The font family name. Possible values are:
     *                           'Times New Roman', 'Arial', 'Courier'.
     */
    public function setFontFamily($fontFamily)
    {
        $this->fontName = $fontFamily;
    }
}
