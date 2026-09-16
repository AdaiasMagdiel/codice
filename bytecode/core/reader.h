#ifndef CODICE_READER_H
#define CODICE_READER_H

#include <stdint.h>
#include <stdio.h>

uint8_t read_u8(FILE *fp);
uint16_t read_u16(FILE *fp);
uint32_t read_u32(FILE *fp);

#endif
