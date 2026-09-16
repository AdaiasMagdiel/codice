#include "reader.h"

uint8_t read_u8(FILE *fp)
{
    uint8_t val;
    fread(&val, 1, 1, fp);
    return val;
}

uint16_t read_u16(FILE *fp)
{
    uint8_t b[2];
    fread(b, 1, 2, fp);
    return (uint16_t)(b[0] << 8) | b[1];
}

uint32_t read_u32(FILE *fp)
{
    uint8_t b[4];
    fread(b, 1, 4, fp);
    return ((uint32_t)b[0] << 24) |
           ((uint32_t)b[1] << 16) |
           ((uint32_t)b[2] << 8) |
           (uint32_t)b[3];
}
