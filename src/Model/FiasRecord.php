<?php

namespace App\Model;

class FiasRecord
{
    public function __construct(
        public readonly string $aoid,
        public readonly string $formalname,
        public readonly string $regioncode,
        public readonly string $offname,
        public readonly string $postalcode,
        public readonly int $aolevel,
        public readonly string $parentguid,
        public readonly string $aoguid,
        public readonly string $shortname,
        public readonly int $actstatus,
        public readonly string $startdate,
        public readonly string $enddate
    ) {
        // Инициализируем поля иерархии
        $this->region_name = '';
        $this->region_shortname = '';
        $this->city_name = '';
        $this->city_shortname = '';
        $this->street_name = '';
        $this->street_shortname = '';
        $this->house_name = '';
        $this->house_shortname = '';
    }

    // Поля для иерархии
    public string $region_name = '';
    public string $region_shortname = '';
    public string $city_name = '';
    public string $city_shortname = '';
    public string $street_name = '';
    public string $street_shortname = '';
    public string $house_name = '';
    public string $house_shortname = '';

    public static function fromArray(array $data): self
    {
        return new self(
            aoid: $data['aoid'],
            formalname: $data['formalname'],
            regioncode: $data['regioncode'],
            offname: $data['offname'],
            postalcode: $data['postalcode'],
            aolevel: (int) $data['aolevel'],
            parentguid: $data['parentguid'],
            aoguid: $data['aoguid'],
            shortname: $data['shortname'],
            actstatus: (int) $data['actstatus'],
            startdate: $data['startdate'],
            enddate: $data['enddate']
        );
    }
}