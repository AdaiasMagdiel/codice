#define NOB_IMPLEMENTATION
#include "nob.h"

int main(int argc, char **argv)
{
    NOB_GO_REBUILD_URSELF(argc, argv);

    Nob_Cmd cmd = {0};
    nob_cmd_append(&cmd, "cc", "-Wall", "-Wextra", "-o", "codice", "codice.c");

    if (!nob_cmd_run(&cmd)) return 1;

    nob_cmd_append(&cmd, "./codice");
    if (!nob_cmd_run(&cmd)) return 1;

    return 0;
}
