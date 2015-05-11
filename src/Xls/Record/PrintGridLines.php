<?php
namespace Xls\Record;

class PrintGridLines extends AbstractRecord
{
    const NAME = 'PRINTGRIDLINES';
    const ID = 0x2B;
    const LENGTH = 0x02;

    /**
     * @param $printGridLines
     *
     * @return string
     */
    public function getData($printGridLines)
    {
        $data = pack("v", intval($printGridLines));

        return $this->getHeader() . $data;
    }
}
