-- Создание таблицы d_fias_addrobj
CREATE TABLE IF NOT EXISTS d_fias_addrobj (
    aoid char(36) NOT NULL,
    formalname varchar(120) NOT NULL,
    regioncode varchar(2) NOT NULL,
    autocode char(1) NOT NULL,
    areacode varchar(3) NOT NULL,
    citycode varchar(3) NOT NULL,
    ctarcode varchar(3) NOT NULL,
    placecode varchar(3) NOT NULL,
    streetcode varchar(4) NOT NULL,
    extrcode varchar(4) NOT NULL,
    sextcode varchar(3) NOT NULL,
    offname varchar(120) NOT NULL,
    postalcode char(6) NOT NULL,
    ifnsfl varchar(4) NOT NULL,
    terrifnsfl varchar(4) NOT NULL,
    ifnsul varchar(4) NOT NULL,
    terrifnsul varchar(4) NOT NULL,
    okato varchar(11) NOT NULL,
    oktmo varchar(8) NOT NULL,
    updatedate date NOT NULL,
    shortname varchar(10) NOT NULL,
    aolevel integer NOT NULL,
    parentguid char(36) NOT NULL,
    aoguid varchar(36) NOT NULL,
    previd varchar(36) NOT NULL,
    nextid varchar(36) NOT NULL,
    code varchar(17) NOT NULL,
    plaincode varchar(15) NOT NULL,
    actstatus smallint NOT NULL,
    centstatus integer NOT NULL,
    operstatus integer NOT NULL,
    currstatus integer NOT NULL,
    startdate date NOT NULL,
    enddate date NOT NULL,
    normdoc varchar(36) NOT NULL,
    PRIMARY KEY (aoid)
);

COMMENT ON TABLE d_fias_addrobj IS 'Классификатор адресообразующих элементов';
COMMENT ON COLUMN d_fias_addrobj.aoid IS 'Уникальный идентификатор записи';
COMMENT ON COLUMN d_fias_addrobj.formalname IS 'Формализованное наименование';
COMMENT ON COLUMN d_fias_addrobj.regioncode IS 'Код региона';
COMMENT ON COLUMN d_fias_addrobj.autocode IS 'Код автономии';
COMMENT ON COLUMN d_fias_addrobj.areacode IS 'Код района';
COMMENT ON COLUMN d_fias_addrobj.citycode IS 'Код города';
COMMENT ON COLUMN d_fias_addrobj.ctarcode IS 'Код внутригородского района';
COMMENT ON COLUMN d_fias_addrobj.placecode IS 'Код населенного пункта';
COMMENT ON COLUMN d_fias_addrobj.streetcode IS 'Код улицы';
COMMENT ON COLUMN d_fias_addrobj.extrcode IS 'Код дополнительного адресообразующего элемента';
COMMENT ON COLUMN d_fias_addrobj.sextcode IS 'Код подчиненного дополнительного адресообразующего элемента';
COMMENT ON COLUMN d_fias_addrobj.offname IS 'Официальное наименование';
COMMENT ON COLUMN d_fias_addrobj.postalcode IS 'Почтовый индекс';
COMMENT ON COLUMN d_fias_addrobj.ifnsfl IS 'Код ИФНС ФЛ';
COMMENT ON COLUMN d_fias_addrobj.terrifnsfl IS 'Код территориального участка ИФНС ФЛ';
COMMENT ON COLUMN d_fias_addrobj.ifnsul IS 'Код ИФНС ЮЛ';
COMMENT ON COLUMN d_fias_addrobj.terrifnsul IS 'Код территориального участка ИФНС ЮЛ';
COMMENT ON COLUMN d_fias_addrobj.okato IS 'ОКАТО';
COMMENT ON COLUMN d_fias_addrobj.oktmo IS 'ОКТМО';
COMMENT ON COLUMN d_fias_addrobj.updatedate IS 'Дата внесения записи';
COMMENT ON COLUMN d_fias_addrobj.shortname IS 'Краткое наименование типа объекта';
COMMENT ON COLUMN d_fias_addrobj.aolevel IS 'Уровень адресного объекта';
COMMENT ON COLUMN d_fias_addrobj.parentguid IS 'Идентификатор объекта родительского объекта';
COMMENT ON COLUMN d_fias_addrobj.aoguid IS 'Глобальный уникальный идентификатор адресного объекта';
COMMENT ON COLUMN d_fias_addrobj.previd IS 'Идентификатор записи связывания с предыдущей исторической записью';
COMMENT ON COLUMN d_fias_addrobj.nextid IS 'Идентификатор записи связывания с последующей исторической записью';
COMMENT ON COLUMN d_fias_addrobj.code IS 'Код адресного объекта одной строкой с признаком актуальности из КЛАДР 4.0';
COMMENT ON COLUMN d_fias_addrobj.plaincode IS 'Код адресного объекта из КЛАДР 4.0 одной строкой без признака актуальности (последних двух цифр)';
COMMENT ON COLUMN d_fias_addrobj.actstatus IS 'Статус актуальности адресного объекта ФИАС. Актуальный адрес на текущую дату. Обычно последняя запись об адресном объекте.';
COMMENT ON COLUMN d_fias_addrobj.centstatus IS 'Статус центра';
COMMENT ON COLUMN d_fias_addrobj.operstatus IS 'Статус действия над записью – причина появления записи';
COMMENT ON COLUMN d_fias_addrobj.currstatus IS 'Статус актуальности КЛАДР 4 (последние две цифры в коде)';
COMMENT ON COLUMN d_fias_addrobj.startdate IS 'Начало действия записи';
COMMENT ON COLUMN d_fias_addrobj.enddate IS 'Окончание действия записи';
COMMENT ON COLUMN d_fias_addrobj.normdoc IS 'Внешний ключ на нормативный документ';

CREATE INDEX IF NOT EXISTS idx_fias_addrobj_aoguid ON d_fias_addrobj(aoguid);
CREATE INDEX IF NOT EXISTS idx_fias_addrobj_parentguid ON d_fias_addrobj(parentguid);
CREATE INDEX IF NOT EXISTS idx_fias_addrobj_regioncode ON d_fias_addrobj(regioncode);
CREATE INDEX IF NOT EXISTS idx_fias_addrobj_aolevel ON d_fias_addrobj(aolevel);
CREATE INDEX IF NOT EXISTS idx_fias_addrobj_actstatus ON d_fias_addrobj(actstatus);
CREATE INDEX IF NOT EXISTS idx_fias_addrobj_updatedate ON d_fias_addrobj(updatedate); 