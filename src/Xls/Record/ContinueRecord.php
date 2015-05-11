<?php

namespace Xls\Record;

class ContinueRecord extends AbstractRecord
{
    const NAME = 'CONTINUE';
    const ID = 0x003C;

    /**
     * Excel limits the size of BIFF records. In Excel 5 the limit is 2084 bytes. In
     * Excel 97 the limit is 8228 bytes. Records that are longer than these limits
     * must be split up into CONTINUE blocks.
     *
     * This function takes a long BIFF record and inserts CONTINUE records as
     * necessary.
     *
     * @param string $data The original binary data to be written
     * @param int $limit BIFF format-specific limit
     * @return string Сonvenient string of continue blocks
     */
    public function getData($data, $limit)
    {
        //reserve 4 bytes for header
        $limit = $limit - 4;

        // The first bytes below limit remain intact. However, we have to change
        // the length field of the record.
        $recordId = substr($data, 0, 2);
        $newRecordSize = $limit - 4;
        $recordData = substr($data, 4, $newRecordSize);
        $result = $recordId . pack("v", $newRecordSize) . $recordData;

        $header = $this->getHeader($limit);

        // Retrieve chunks of 2080/8224 bytes +4 for the header
        $dataLength = strlen($data);
        for ($i = $limit; $i < $dataLength - $limit; $i += $limit) {
            $result .= $header;
            $result .= substr($data, $i, $limit);
        }

        // Retrieve the last chunk of data
        $header = $this->getHeader($dataLength - $i);
        $result .= $header;
        $result .= substr($data, $i, $dataLength - $i);

        return $result;
    }
}
