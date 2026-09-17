#define NOB_IMPLEMENTATION
#include "libs/nob.h"

int main(int argc, char **argv)
{
    NOB_GO_REBUILD_URSELF(argc, argv);

#ifdef _WIN32
    const char *output = "./dist/codice.exe";
#else
    const char *output = "./dist/codice";
#endif

    Nob_Cmd cmd = {0};
    nob_cmd_append(
        &cmd,
        "cc",
        "-Wall",
        "-Wextra",
        "-o",
        output,
        "codice.c",
        "core/vm.c",
        "core/reader.c",
        "core/builtins.c",
        "core/builtins/stampa.c");

    if (!nob_cmd_run(&cmd))
        return 1;

    return 0;
}
