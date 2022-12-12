//
//  RetryButtonView.swift
//  Voting
//
//  Created by Szabolcs Tóth on 24.11.2022.
//  Copyright © 2022 Szabolcs Tóth. All rights reserved.
//

import SwiftUI

struct RetryButtonView: View {
    var body: some View {
        content
    }
    
    @ViewBuilder var content: some View {
        Text("Retry")
            .foregroundColor(.black)
            .textCase(.uppercase)
            .bold()
            .padding(.horizontal, 20)
            .padding(.vertical, 10)
            .background(.white)
            .cornerRadius(4)
    }
}

struct ButtonView_Previews: PreviewProvider {
    static var previews: some View {
        RetryButtonView()
    }
}
