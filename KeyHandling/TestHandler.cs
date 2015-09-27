﻿using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Windows.Forms;

namespace aevvuploader.KeyHandling
{
    class TestHandler : IInputHandler
    {
        public void Handle(IScreenshottableForm form, KeyboardHook hook)
        {
            form.ToggleVisibility();
        }

        public Keys TriggerKey => Keys.T;
    }
}
