-- Основной составной индекс для поиска по уровню и статусу
CREATE INDEX IF NOT EXISTS idx_fias_addrobj_aolevel_actstatus
ON d_fias_addrobj (aolevel, actstatus) 
WHERE actstatus = 1;

-- Индекс для поиска по региону (формализованное имя + уровень + статус)
CREATE INDEX IF NOT EXISTS idx_fias_addrobj_formalname_aolevel_actstatus 
ON d_fias_addrobj (formalname, aolevel, actstatus) 
WHERE actstatus = 1;

-- Индекс для поиска по коду региона + уровень + статус
CREATE INDEX IF NOT EXISTS idx_fias_addrobj_regioncode_aolevel_actstatus 
ON d_fias_addrobj (regioncode, aolevel, actstatus) 
WHERE actstatus = 1;

-- Индекс для поиска по GUID + уровень + статус (для JOIN'ов)
CREATE INDEX IF NOT EXISTS idx_fias_addrobj_aoguid_aolevel_actstatus 
ON d_fias_addrobj (aoguid, aolevel, actstatus) 
WHERE actstatus = 1;

-- Индекс для поиска по parentguid + уровень + статус (для JOIN'ов)
CREATE INDEX IF NOT EXISTS idx_fias_addrobj_parentguid_aolevel_actstatus 
ON d_fias_addrobj (parentguid, aolevel, actstatus) 
WHERE actstatus = 1;

-- Частичный индекс для регионов (aolevel = 1)
CREATE INDEX IF NOT EXISTS idx_fias_addrobj_regions 
ON d_fias_addrobj (regioncode, formalname, shortname) 
WHERE aolevel = 1 AND actstatus = 1;

-- Частичный индекс для городов (aolevel = 4)
CREATE INDEX IF NOT EXISTS idx_fias_addrobj_cities 
ON d_fias_addrobj (aoguid, parentguid, formalname, shortname) 
WHERE aolevel = 4 AND actstatus = 1;

-- Частичный индекс для улиц (aolevel = 7)
CREATE INDEX IF NOT EXISTS idx_fias_addrobj_streets 
ON d_fias_addrobj (aoguid, parentguid, formalname, shortname) 
WHERE aolevel = 7 AND actstatus = 1;

-- Частичный индекс для домов (aolevel = 8)
CREATE INDEX IF NOT EXISTS idx_fias_addrobj_houses 
ON d_fias_addrobj (aoguid, parentguid, formalname, shortname) 
WHERE aolevel = 8 AND actstatus = 1;

-- Составной индекс для поиска по региону и формализованному имени
CREATE INDEX IF NOT EXISTS idx_fias_addrobj_regioncode_formalname 
ON d_fias_addrobj (regioncode, formalname) 
WHERE actstatus = 1;

-- Индекс для поиска по формализованному имени с триграммами (для ILIKE)
CREATE INDEX IF NOT EXISTS idx_fias_addrobj_formalname_gin 
ON d_fias_addrobj USING gin (formalname gin_trgm_ops) 
WHERE actstatus = 1;

-- Дополнительные индексы для оптимизации CTE подзапросов
-- Индекс для быстрого поиска по aolevel = 1 (регионы)
CREATE INDEX IF NOT EXISTS idx_fias_addrobj_aolevel_1 
ON d_fias_addrobj (regioncode, formalname, shortname) 
WHERE aolevel = 1 AND actstatus = 1;

-- Индекс для быстрого поиска по aolevel = 4 (города)
CREATE INDEX IF NOT EXISTS idx_fias_addrobj_aolevel_4 
ON d_fias_addrobj (aoguid, formalname, shortname) 
WHERE aolevel = 4 AND actstatus = 1;

-- Индекс для быстрого поиска по aolevel = 7 (улицы)
CREATE INDEX IF NOT EXISTS idx_fias_addrobj_aolevel_7 
ON d_fias_addrobj (aoguid, formalname, shortname) 
WHERE aolevel = 7 AND actstatus = 1;

-- Индекс для быстрого поиска по aolevel = 8 (дома)
CREATE INDEX IF NOT EXISTS idx_fias_addrobj_aolevel_8 
ON d_fias_addrobj (aoguid, formalname, shortname) 
WHERE aolevel = 8 AND actstatus = 1;

-- Составной индекс для основного запроса (aolevel IN (4,7))
CREATE INDEX IF NOT EXISTS idx_fias_addrobj_main_search 
ON d_fias_addrobj (aolevel, actstatus, formalname, regioncode, parentguid, aoguid) 
WHERE aolevel IN (4, 7) AND actstatus = 1;

COMMENT ON INDEX idx_fias_addrobj_aolevel_actstatus IS 'Основной индекс для фильтрации по уровню и статусу';
COMMENT ON INDEX idx_fias_addrobj_formalname_aolevel_actstatus IS 'Индекс для поиска по названию объекта';
COMMENT ON INDEX idx_fias_addrobj_regioncode_aolevel_actstatus IS 'Индекс для поиска по коду региона';
COMMENT ON INDEX idx_fias_addrobj_aoguid_aolevel_actstatus IS 'Индекс для JOIN по GUID';
COMMENT ON INDEX idx_fias_addrobj_parentguid_aolevel_actstatus IS 'Индекс для JOIN по parent GUID';
COMMENT ON INDEX idx_fias_addrobj_regions IS 'Частичный индекс для регионов';
COMMENT ON INDEX idx_fias_addrobj_cities IS 'Частичный индекс для городов';
COMMENT ON INDEX idx_fias_addrobj_streets IS 'Частичный индекс для улиц';
COMMENT ON INDEX idx_fias_addrobj_houses IS 'Частичный индекс для домов';
COMMENT ON INDEX idx_fias_addrobj_regioncode_formalname IS 'Составной индекс для поиска по региону и названию';
COMMENT ON INDEX idx_fias_addrobj_formalname_gin IS 'GIN индекс для полнотекстового поиска по названию';
COMMENT ON INDEX idx_fias_addrobj_aolevel_1 IS 'Оптимизированный индекс для регионов (CTE)';
COMMENT ON INDEX idx_fias_addrobj_aolevel_4 IS 'Оптимизированный индекс для городов (CTE)';
COMMENT ON INDEX idx_fias_addrobj_aolevel_7 IS 'Оптимизированный индекс для улиц (CTE)';
COMMENT ON INDEX idx_fias_addrobj_aolevel_8 IS 'Оптимизированный индекс для домов (CTE)';
COMMENT ON INDEX idx_fias_addrobj_main_search IS 'Составной индекс для основного поиска'; 